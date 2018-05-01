<?php

namespace AppBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use AppBundle\Entity\Submission;
use AppBundle\Entity\Problem;
use AppBundle\Entity\ProblemLanguage;
use AppBundle\Entity\Language;
use AppBundle\Entity\UserSectionRole;
use AppBundle\Entity\Testcase;

use AppBundle\Utils\Grader;
use AppBundle\Utils\Zipper;

use Psr\Log\LoggerInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Config\Definition\Exception\Exception;

class ProblemController extends Controller {

 	public function editAction($sectionId, $assignmentId, $problemId) {
		$em = $this->getDoctrine()->getManager();
		$qb = $em->createQueryBuilder();

		$qb->select('l')
			->from('AppBundle\Entity\Language', 'l')
			->where('1 = 1');
		$languages = $qb->getQuery()->getResult();
		
		if(!isset($sectionId) || !($sectionId > 0)){
			die("SECTION ID WAS NOT PROVIDED OR NOT FORMATTED PROPERLY");
		}
		
		$section = $em->find('AppBundle\Entity\Section', $sectionId);
		if(!$section){
			die("SECTION DOES NOT EXIST");
		}
		
		# REDIRECT TO CONTEST_PROBLEM_EDIT IF NEED BE
		if($section->course->is_contest){
			return $this->redirectToRoute('contest_problem_edit', ['contestId' => $sectionId, 'roundId' => $assignmentId, 'problemId' => $problemId]);
		}
		
		if(!isset($assignmentId) || !($assignmentId > 0)){
			die("ASSIGNMENT ID WAS NOT PROVIDED OR NOT FORMATTED PROPERLY");
		}
		
		$assignment = $em->find('AppBundle\Entity\Assignment', $assignmentId);
		if(!$assignment){
			die("SECTION DOES NOT EXIST");
		}
		
		if($problemId != 0){
			
			if(!isset($problemId) || !($problemId > 0)){
				die("PROBLEM ID WAS NOT PROVIDED OR NOT FORMATTED PROPERLY");
			}		
			
			$problem = $em->find('AppBundle\Entity\Problem', $problemId);
			
			if(!$problem){
				die("PROBLEM DOES NOT EXIST");
			}			
		}
		
		$ace_modes = [];
		$filetypes = [];
		foreach($languages as $l){
			
			$ace_modes[$l->name] = $l->ace_mode;
			$filetypes[str_replace(".", "", $l->filetype)] = $l->name;
		}

		return $this->render('problem/edit.html.twig', [
			'languages' => $languages,
			'section' => $section,
			'assignment' => $assignment,
			'problem' => $problem,
			
			'ace_modes' => $ace_modes,
			'filetypes' => $filetypes,
			
			'edit_route' => true,
		]);
    }

	public function deleteAction($sectionId, $assignmentId, $problemId){

		$em = $this->getDoctrine()->getManager();
		
		if(!isset($problemId) || !($problemId > 0)){
			die("PROBLEM ID WAS NOT PROVIDED OR NOT FORMATTED PROPERLY");
		}

		$problem = $em->find('AppBundle\Entity\Problem', $problemId);
		if(!$problem){
			die("PROBLEM DOES NOT EXIST");
		}

		$user = $this->get('security.token_storage')->getToken()->getUser();
		if(!$user){
			die("USER DOES NOT EXIST");
		}

		# validate the user
		$grader = new Grader($em);
		if(!$user->hasRole("ROLE_SUPER") && !$user->hasRole("ROLE_ADMIN") && !$grader->isTeaching($user, $problem->assignment->section)){
			die("YOU ARE NOT ALLOWED TO DELETE THIS PROBLEM");
		}

		$problems = $em->getRepository('AppBundle\Entity\Problem')->findBy(['clone_hash'=>$problem->clone_hash]);
		
		foreach($problems as &$prob){
			$em->remove($prob);
		}
		$em->flush();

		return $this->redirectToRoute('assignment', ['sectionId' => $problem->assignment->section->id, 'assignmentId' => $problem->assignment->id]);
	}

	public function modifyPostAction(Request $request) {

		$em = $this->getDoctrine()->getManager();

		# validate the current user
		$user = $this->get('security.token_storage')->getToken()->getUser();
		if(!$user){
			return $this->returnForbiddenResponse("You are not a user.");
		}

		# see which fields were included
		$postData = $request->request->all();

		# get the current assignment
		if(!isset($postData['assignmentId']) || !($postData['assignmentId'] > 0)){
			die("ASSIGNMENT ID WAS NOT PROVIDED OR NOT FORMATTED PROPERLY");
		}
		
		$assignment = $em->find('AppBundle\Entity\Assignment', $postData['assignmentId']);
		if(!$assignment){
			return $this->returnForbiddenResponse("Assignment ".$postData['assignmentId']." does not exist");
		}

		# only super users/admins/teacher can make/edit an assignment
		$grader = new Grader($em);
		if(!$user->hasRole("ROLE_SUPER") && !$user->hasRole("ROLE_ADMIN") && !$grader->isTeaching($user, $assignment->section)){
			return $this->returnForbiddenResponse("You do not have permission to make a problem.");
		}
		
		$em->getConnection()->beginTransaction();

		try{

			$assignments = $em->getRepository('AppBundle\Entity\Assignment')->findBy(['clone_hash'=>$assignment->clone_hash]);
			$problems = [];
	
			# get the problem or create a new one
			if($postData['problem'] == 0){
	
				$temp = [''];
				while(count($temp) > 0){
					$hash = random_int(-2147483648, 2147483647);
	
					$temp = $em->getRepository('AppBundle\Entity\Problem')->findBy(['clone_hash'=>$hash]);	
				}
	
				foreach($assignments as $asgn){
					$prob = new Problem();
					
					$prob->assignment = $asgn;
					$prob->clone_hash = $hash;
	
					$em->persist($prob);
	
					$problems[] = $prob;
				}
	
			} else {
				
				if(!isset($postData['problem']) || !($postData['problem'] > 0)){
					throw new Exception("PROBLEM ID WAS NOT PROVIDED OR NOT FORMATTED PROPERLY");
				}
	
				$prob = $em->find('AppBundle\Entity\Problem', $postData['problem']);
	
				if(!$prob || $assignment != $prob->assignment){
					throw new Exception("Problem ".$postData['problem']." does not exist for the given assignment.");
				}
			
				$problems = $em->getRepository('AppBundle\Entity\Problem')->findBy(['clone_hash'=>$prob->clone_hash]);		
			}
	
			# check mandatory fields
			if(!isset($postData['name']) || trim($postData['name']) == "" || !isset($postData['description']) || trim($postData['description']) == "" || !isset($postData['weight']) || !isset($postData['time_limit'])){
	
				throw new Exception("Not every necessary field was provided");
	
			} else {
	
				if(!is_numeric(trim($postData['weight'])) || (int)trim($postData['weight']) < 0){
					throw new Exception("Weight provided is not valid - it must be non-negative");
				}
	
				if(!is_numeric(trim($postData['time_limit'])) || (int)trim($postData['time_limit']) < 1){
					throw new Exception("Time limit provided is not valid - it must be greater than 0. You gave us: ". $postData['time_limit']);
				}
	
			}

			# check the optional fields
			# attempt penalties
			$total_attempts = $postData['total_attempts'];
			$attempts_before_penalty = $postData['attempts_before_penalty'];
			$penalty_per_attempt = $postData['penalty_per_attempt'];

			if(!isset($total_attempts) || !is_numeric($total_attempts) || !isset($attempts_before_penalty) || !is_numeric($attempts_before_penalty) || !isset($penalty_per_attempt) || !is_numeric($penalty_per_attempt)){
				throw new Exception("Not every necessary grading method flag was set properly");
			}

			if($total_attempts < $attempts_before_penalty){
				throw new Exception("Attempts before penalty must be greater than the total attempts");
			}

			if($penalty_per_attempt < 0.00 || $penalty_per_attempt > 1.00){
				throw new Exception("Penalty per attempts must be between 0 and 1");
			}

			if(!isset($postData['languages']) || !isset($postData['testcases'])){

				throw new Exception("Languages or testcases were not provided");

			} else {

				if(count($postData['languages']) < 1) throw new Exception("You must specify at least one language");

				if(count($postData['testcases']) < 1) throw new Exception("You must specify at least one test case");

			}

			# feedback flags
			$stop_on_first_fail = $postData['stop_on_first_fail'];
			$response_level = trim($postData['response_level']);
			$display_testcaseresults = $postData['display_testcaseresults'];
			$testcase_output_level = trim($postData['testcase_output_level']);
			$extra_testcases_display = $postData['extra_testcases_display'];

			if($stop_on_first_fail != null || $response_level != null || $display_testcaseresults != null || $testcase_output_level != null || $extra_testcases_display != null){

				if($stop_on_first_fail == null || $response_level == null || $display_testcaseresults == null || $testcase_output_level == null || $extra_testcases_display == null){
					throw new Exception("Not every necessary feedback flag was set");
				}

				if($response_level != "Long" && $response_level != "Short" && $response_level != "None"){
					throw new Exception("Response level is not a valid string value");
				}

				if($testcase_output_level != "Both" && $testcase_output_level != "Output" && $testcase_output_level != "None"){
					throw new Exception("Testcase output level is not a valid string value. You gave: " . $testcase_output_level);
				}

			} else {
				$stop_on_first_fail = false;
				$response_level = "Long";
				$display_testcaseresults = true;
				$testcase_output_level = "Both";
				$extra_testcases_display = true;
			}

			$custom_validator = trim($postData['custom_validator']);			
			if( !(isset($custom_validator) && $custom_validator != "") ){
				$custom_validator = null;	
			}

			$allow_multiple = $postData['allow_multiple'];
			$allow_upload = $postData['allow_upload'];


			// get the contents of the default code and save it to a file so we can save
			$temp = tmpfile();
			$temp_filename = stream_get_meta_data($temp)['uri'];

			$default_codes = [];

			$langs = $em->getRepository("AppBundle\Entity\Language")->findAll();

			foreach($langs as $l){

				if ($_FILES['file_'.$l->id]['tmp_name'] != null && move_uploaded_file($_FILES['file_'.$l->id]['tmp_name'], $temp_filename)) {

					$fh = fopen($temp_filename, "r");

					if(!$fh){
						throw new Exception('Cant open file.');
					}

					$default_codes[$l->id] = $fh;
				
				} else{

					$default_codes[$l->id] = null;
				}
			}

			foreach($problems as &$problem) {

				$problem->version = $problem->version+1;
				$problem->name = trim($postData['name']);
				$problem->description = trim($postData['description']);
				$problem->weight = (int)trim($postData['weight']);
				$problem->is_extra_credit = ($postData['is_extra_credit'] == "true");		
				$problem->time_limit = (int)trim($postData['time_limit']);
				
				$problem->total_attempts = $total_attempts;
				$problem->attempts_before_penalty = $attempts_before_penalty;
				$problem->penalty_per_attempt = $penalty_per_attempt;

				$problem->stop_on_first_fail = ($stop_on_first_fail == "true");
				$problem->response_level = $response_level;
				$problem->display_testcaseresults = ($display_testcaseresults == "true");
				$problem->testcase_output_level = $testcase_output_level;
				$problem->extra_testcases_display = ($extra_testcases_display == "true");	
				
				# allow adding files (tabs)
				$problem->allow_multiple = ($allow_multiple == "true");

				# allow uploading files
				$problem->allow_upload = ($allow_upload == "true");
				
				# custom validator
				$problem->custom_validator = $custom_validator;	

				# go through the problemlanguages
				# remove the old ones
				$oldDefaultCode = [];

				foreach($problem->problem_languages as $pl){
					$oldDefaultCode[$pl->language->id] = $pl->default_code;
					$em->remove($pl);
				}

				$newProblemLanguages = [];
				$decodedLanguages = json_decode($postData['languages']);
				foreach($decodedLanguages as $l){

					//return $this->returnForbiddenResponse(var_dump($decodedLanguages));
					if(!isset($l->id) || !($l->id > 0)){				
						throw new Exception("You did not specify a language id");
					}

					$language = $em->find("AppBundle\Entity\Language", $l->id);

					if(!$language){
						throw new Exception("Provided language with id ".$l->id." does not exist");
					}

					$problemLanguage = new ProblemLanguage();

					$problemLanguage->language = $language;
					$problemLanguage->problem = $problem;
					
					// set compiler options and default code
					if(isset($l->compiler_options) && strlen($l->compiler_options) > 0){
						
						# check the compiler options for invalid characters
						if(preg_match("/^[ A-Za-z0-9+=\-]+$/", $l->compiler_options) != 1){
							throw new Exception("The compiler options provided has invalid characters");
						}
										
						$problemLanguage->compilation_options = $l->compiler_options;
					}
					
					$default_code = $default_codes[$l->id];

					if(isset($default_code)){

						$def_name = stream_get_meta_data($default_code)['uri'];

						$tmpfname = tempnam("/tmp", "grd");
						
						if(copy($def_name, $tmpfname)){

							$fh = fopen($tmpfname, "r");
							
							$problemLanguage->default_code = $fh;

						} else {
							throw new Exception("Error creating temp file");
						}						
						
					} else if(isset($oldDefaultCode[$language->id])){
						
						$problemLanguage->default_code = $oldDefaultCode[$language->id];
					
					} else if(isset($l->default_code) && strlen($l->default_code) > 0){
						
						$problemLanguage->default_code = $l->default_code;
					} 
										
					$newProblemLanguages[] = $problemLanguage;
					$em->persist($problemLanguage);
				}

				# testcases
				# set the old testcases to null 
				# (so they don't go away and can be accessed in the results page)
				foreach($problem->testcases as &$testcase){
					$testcase->problem = null;
					$em->persist($testcase);
				}
				
				$newTestcases = new ArrayCollection();
				$count = 1;

				$decodedTestcases = json_decode($postData['testcases']);
				foreach($decodedTestcases as &$tc){
					
					$tc = (array) $tc;
					
					# build the testcase
					try{				
						$testcase = new Testcase($problem, $tc, $count);
						$count++;
							
						$em->persist($testcase);
						$newTestcases->add($testcase);
						
					} catch(Exception $e){
						throw new Exception($e->getMessage());
					}

				}
				$problem->testcases = $newTestcases;
				$problem->testcase_counts[] = count($problem->testcases);
			}

			$em->flush();
			$em->getConnection()->commit();

		} catch(Exception $e) {

			$em->getConnection()->rollBack();
			return $this->returnForbiddenResponse($e->getMessage());
		}

		$url = $this->generateUrl('assignment', ['sectionId' => $prob->assignment->section->id, 'assignmentId' => $prob->assignment->id, 'problemId' => $prob->id]);
		
		return new JsonResponse(array("problemId"=> $prob, "redirect_url" => $url));
	}

	public function resultAction($submission_id) {

		$em = $this->getDoctrine()->getManager();
		$grader = new Grader($em);
		
		if(!isset($submission_id) || !($submission_id > 0)){
			die("SUBMISSION ID WAS NOT PROVIDED OR NOT FORMATTED PROPERLY");
		}

		$submission = $em->find("AppBundle\Entity\Submission", $submission_id);

		if(!$submission){
			die("SUBMISSION DOES NOT EXIST");
		}
		
		# REDIRECT TO CONTEST IF NEED BE
		if($submission->problem->assignment->section->course->is_contest){
			return $this->redirectToRoute('contest_result', [
				'contestId' => $submission->problem->assignment->section->id, 
				'roundId' => $submission->problem->assignment->id, 
				'problemId' => $submission->problem->id, 
				'resultId' => $submission->id
			]);
		}

		# get the user
		$user = $this->get('security.token_storage')->getToken()->getUser();
		if(!$user){
			die("USER DOES NOT EXIST!");
		}

		# make sure the user has permissions to view the submission result
		if(!$user->hasRole("ROLE_SUPER") && !$user->hasRole("ROLE_ADMIN") && !$grader->isTeaching($user, $submission->problem->assignment->section) && !$grader->isOnTeam($user, $submission->problem->assignment, $submission->team)){
			echo "YOU ARE NOT ALLOWED TO VIEW THIS SUBMISSION";
			die();
		}

		$grader = new Grader($em);
		$feedback = $grader->getFeedback($submission);
				
		$ace_mode = $submission->language->ace_mode;
		
		$qb_user = $em->createQueryBuilder();
		$qb_user->select('usr')
			->from('AppBundle\Entity\UserSectionRole', 'usr')
			->where('usr.section = ?1')
			->setParameter(1, $submission->problem->assignment->section);

		$user_query = $qb_user->getQuery();
		$usersectionroles = $user_query->getResult();

		$section_takers = [];

		foreach($usersectionroles as $usr){
			if($usr->role->role_name == "Takes"){
				$section_takers[] = $usr->user;
			}
		}
				
		return $this->render('problem/result.html.twig', [
		
			'section' => $submission->problem->assignment->section,
			'assignment' => $submission->problem->assignment,
			'problem' => $submission->problem,
			'submission' => $submission,
						
			'user_impersonators' => $section_takers,
			'grader' => new Grader($em),
			
			'result_page' => true,
			'result_route' => true, 
			'feedback' => $feedback,

			'ace_mode' => $ace_mode,
		]);
	}
	
	
	public function resultDeleteAction($submission_id){
		
		$em = $this->getDoctrine()->getManager();
		$grader = new Grader($em);
		
		if(!isset($submission_id) || !($submission_id > 0)){
			die("SUBMISSION ID WAS NOT PROVIDED OR NOT FORMATTED PROPERLY");
		}

		$submission = $em->find("AppBundle\Entity\Submission", $submission_id);

		if(!$submission){
			die("SUBMISSION DOES NOT EXIST");
		}

		# get the user
		$user = $this->get('security.token_storage')->getToken()->getUser();
		if(!$user){
			die("USER DOES NOT EXIST!");
		}

		# make sure the user has permissions to view the submission result
		if(!$user->hasRole("ROLE_SUPER") && !$grader->isTeaching($user, $submission->problem->assignment->section)){
			echo "YOU ARE NOT ALLOWED TO DELETE THIS SUBMISSION";
			die();
		}
		
		
		$em->remove($submission);
		$em->flush();
		
		return $this->redirectToRoute('assignment', ['problemId' => $submission->problem->id, 'assignmentId' => $submission->problem->assignment->id, 'sectionId' => $submission->problem->assignment->section->id]);	
	}
	
	private function returnForbiddenResponse($message){
		$response = new Response($message);
		$response->setStatusCode(Response::HTTP_FORBIDDEN);
		return $response;
	}
}





?>
