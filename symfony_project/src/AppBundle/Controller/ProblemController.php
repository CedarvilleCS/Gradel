<?php

namespace AppBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use AppBundle\Entity\Submission;
use AppBundle\Entity\Problem;
use AppBundle\Entity\ProblemLanguage;
use AppBundle\Entity\UserSectionRole;

use AppBundle\Utils\Grader;
use AppBundle\Utils\TestCaseCreator;

use Psr\Log\LoggerInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Doctrine\Common\Collections\ArrayCollection;

class ProblemController extends Controller {

    public function newAction($sectionId, $assignmentId) {
      $em = $this->getDoctrine()->getManager();
      $qb = $em->createQueryBuilder();

      $qb->select('l')
        ->from('AppBundle\Entity\Language', 'l')
        ->where('1 = 1');

      $languages = $qb->getQuery()->getResult();

      return $this->render('problem/new.html.twig', [
        'languages' => $languages,
        'sectionId' => $sectionId,
        'assignmentId' => $assignmentId,
      ]);
    }

	public function insertAction(Request $request) {
      $em = $this->getDoctrine()->getManager();
      $user = $this->get('security.token_storage')->getToken()->getUser();
      $post_data = $request->request->all();
      
	  
	  $grader = new Grader($em);		
	  if(!$user->hasRole("ROLE_SUPER") && !$user->hasRole("ROLE_ADMIN") && !$grader->isTeaching($user, $section)){			
		  return new JsonResponse(array("errors"=> ["You do not have permission to create a problem"], "problemId"=> $problem->id));
	  }		  
	  
	  $errors = array();


	  # check the required fields
      $languageArr = $post_data['languages'];
	  $languageEntities = [];
	  
      if (count($languageArr) == 0) {
		  array_push($errors, "You must provide at least one language");
      } else {
		  
		  foreach($languageArr as $lang){
			  // make sure that each language exists
			  $l = $em->find("AppBundle\Entity\Language", $lang);
			  
			  if(!$l){
				  array_push($errors, "Language ".$lang." does not exist.");
			  } else{
				  $languageEntities[] = $l;
			  }
		  }
		  
	  }
	  
	  $testcaseArr = $post_data['testcases'];
	  $testcaseEntities = [];      
	  if (count($testcaseArr) == 0) {
		  array_push($errors, "You must provide at least one testcase");
      }
	  
	  $assignment = $em->find("AppBundle\Entity\Assignment", $post_data['assignmentId']);
	  if (!$assignment) {
		  array_push($errors, "Assignment provided does not exist");
	  }

      $name = $post_data['name'];
      if ($name == "") {
        array_push($errors, "Name must be set");
      }

      $description = $post_data['description'];
      if ($description == "") {
        array_push($errors, "Description must be set");
      }
      
	  $weight = $post_data['weight'];
      if (!is_numeric($weight) || ((int)$weight < 1)) {
        array_push($errors, "You must provide an integer weight greater than 0. You provided: " . $weight);
      }
	  
      $is_extra_credit = $post_data['is_extra_credit'];
      if ($is_extra_credit !== "true" && $is_extra_credit !== "false") {
        array_push($errors, "You are trying to be malicious! Stop it! Extra Credit must be a boolean");
      }
	  
      $time_limit = $post_data['time_limit'];
      if ($time_limit <= 0) {
        array_push($errors, "Time limit must be greater than 0!");
      }
	  
	  # check the optional fields	  
	  # attempt penalties
	  $total_attempts = $post_data['total_attempts'];
	  $attempts_before_penalty = $post_data['attempts_before_penalty'];
	  $penalty_per_attempt = $post_data['penalty_per_attempt'];
	  
	  if($total_attempts != null || $attempts_before_penalty != null || $penalty_per_attempt != null){
		
		if($total_attempts == null || $attempts_before_penalty == null || $penalty_per_attempt == null){
			array_push($errors, "Not every necessary grading method flag was set");
		}
		 
		if($total_attempts < $attempts_before_penalty){
			array_push($errors, "Attempts before penalty must be greater than the total attempts");
		}
		
		if($penalty_per_attempt < 0.00 || $penalty_per_attempt > 1.00){
			array_push($errors, "Penalty per attempts must be between 0 and 1");
		}			
	  } else{
		  $total_attempts = 0;
		  $attempts_before_penalty = 0;
		  $penalty_per_attempt = 0.00;
	  }
	  
	  # feedback flags
	  $stop_on_first_fail = $post_data['stop_on_first_fail'];
	  $response_level = $post_data['response_level'];
	  $display_testcaseresults = $post_data['display_testcaseresults'];
	  $testcase_output_level = $post_data['testcase_output_level'];
	  $extra_testcases_display = $post_data['extra_testcases_display'];
	  
	  if($stop_on_first_fail != null || $response_level != null || $display_testcaseresults != null || $testcase_output_level != null || $extra_testcases_display != null){
		  
		  if($stop_on_first_fail == null || $response_level == null || $display_testcaseresults == null || $testcase_output_level == null || $extra_testcases_display == null){
			array_push($errors, "Not every necessary feedback flag was set");
		  }
		  
		  if($response_level != "Long" && $response_level != "Short" && $response_level != "None"){
			array_push($errors, "Response level is not a valid string value");
		  }
		  
		  if($testcase_output_level != "Both" && $testcase_output_level != "Output" && $testcase_output_level != "None"){
			array_push($errors, "Testcase output level is not a valid string value");
		  }
		  
	  } else {
		  $stop_on_first_fail = false;
		  $response_level = "Long";
		  $display_testcaseresults = true;
		  $testcase_output_level = "Both";
		  $extra_testcases_display = true;
	  }
	  
	  
      if (sizeof($errors) == 0) {

        $problem = new Problem();

        $problem->assignment = $assignment;
        $problem->name = $name;
        $problem->description = $description;
        $problem->weight = $weight;
        $problem->is_extra_credit = ($is_extra_credit == "true");
        $problem->time_limit = $time_limit;
		
		$problem->total_attempts = $total_attempts;
		$problem->attempts_before_penalty = $attempts_before_penalty;
		$problem->penalty_per_attempt = $penalty_per_attempt;
		
		$problem->stop_on_first_fail = ($stop_on_first_fail == "true");
		$problem->response_level = $response_level;
		$problem->display_testcaseresults = ($display_testcaseresults == "true");
		$problem->testcase_output_level = $testcase_output_level;
		$problem->extra_testcases_display = ($extra_testcases_display == "true");

        $em->persist($problem);

		# go through the problemlanguages
        foreach ($languageEntities as $language) {
          		  
          $problemLanguage = new ProblemLanguage();

          $problemLanguage->language = $language;
          $problemLanguage->problem = $problem;
          $em->persist($problemLanguage);		  
        }
		
		# go through the testcases array provided
		$count = 1;
		foreach($testcaseArr as $tc){
			
			if(!is_array($tc)){
				return new JsonResponse(array("errors"=> ["Testcase data is not formatted properly"], "problemId"=> $problem->id));
			}
			
			# build the testcase 
			$response = TestCaseCreator::makeTestCase($em, $problem, $tc, $count);
			$count++;
			
			# check what the makeTestCase returns
			if(!$response->problem){
				return new JsonResponse(array("errors"=> [$response->getContent()], "problemId"=> $problem->id));
			} else{
				$testcase = $response;
			}
			
			$em->persist($testcase);
		}
		  
		$em->flush();
      }

      return new JsonResponse(array("errors"=> $errors, "problemId"=> $problem->id));
    }

    public function editAction() {

      return $this->render('problem/edit.html.twig', [

      ]);
    }
	
	public function deleteAction($sectionId, $assignmentId, $problemId){

		$em = $this->getDoctrine()->getManager();

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

		$em->remove($problem);
		$em->flush();
		return $this->redirectToRoute('assignment', ['sectionId' => $problem->assignment->section->id, 'assignmentId' => $problem->assignment->id]);
	}

	public function resultAction($submission_id) {

		$em = $this->getDoctrine()->getManager();
		$grader = new Grader($em);

		$submission = $em->find("AppBundle\Entity\Submission", $submission_id);

		if(!$submission){
			echo "SUBMISSION DOES NOT EXIST";
			die();
		}

		# get the user
		$user = $this->get('security.token_storage')->getToken()->getUser();
		if(!$user){
			die("USER DOES NOT EXIST!");
		}

		# make sure the user has permissions to view the submission result
		if($user->hasRole("ROLE_SUPER") && !$grader->isTeaching($user, $submission->problem->assignment->section) && !$grader->isOnTeam($user, $submission->problem->assignment, $submission->team)){
			echo "YOU ARE NOT ALLOWED TO VIEW THIS SUBMISSION";
			die();
		}

		$grader = new Grader($em);
		$feedback = $grader->getFeedback($submission);

        return $this->render('problem/result.html.twig', [
			'submission' => $submission,
			'grader' => new Grader($em),
			'result_page' => true,
			'feedback' => $feedback,
        ]);
	}
}





?>
