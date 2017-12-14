<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Role;
use AppBundle\Entity\User;
use AppBundle\Entity\Team;
use AppBundle\Entity\Course;
use AppBundle\Entity\Section;
use AppBundle\Entity\Assignment;
use AppBundle\Entity\Problem;
use AppBundle\Entity\ProblemLanguage;
use AppBundle\Entity\UserSectionRole;
use AppBundle\Entity\Testcase;
use AppBundle\Entity\Submission;
use AppBundle\Entity\Language;
use AppBundle\Entity\AssignmentGradingMethod;
use AppBundle\Entity\Feedback;
use AppBundle\Entity\TestcaseResult;

use AppBundle\Utils\Grader;
use AppBundle\Utils\Uploader;
use AppBundle\Utils\Generator;

use \DateTime;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Psr\Log\LoggerInterface;

class CompilationController extends Controller {	
	
	/* name=submit */
	public function submitAction(Request $request) {
	
		# entity manager
		$em = $this->getDoctrine()->getManager();		
		
		# gets the gradel/symfony_project directory
		$web_dir = $this->get('kernel')->getProjectDir()."/";
			
		$generator = new Generator($em, $web_dir);		
		$grader = new Grader($em);
						
		# get the current user
		$user= $this->get('security.token_storage')->getToken()->getUser();
		
		if(!$user){
			return $this->returnForbiddenResponse("USER DOES NOT EXIST");
		}
		
		# VALIDATION
		
		$postData = $request->request->all();
		
		$problem_id = $postData['problemId'];
		$language_id = $postData['languageId'];
		$submitted_filename = trim($postData['filename']);
		$main_class = trim($postData['mainclass']);
		$package_name = trim($postData['packagename']);
		
		# make sure all the required post params were passed
		if(!isset($problem_id) || !isset($language_id) || !isset($submitted_filename) || !isset($main_class) || !isset($package_name)){
			return $this->returnForbiddenResponse("NOT EVERY NECESSARY FIELD WAS PROVIDED");
		}		
		
		
		# get the current problem
		$problem = $em->find("AppBundle\Entity\Problem", $problem_id);
		if(!$problem){
			return $this->returnForbiddenResponse("PROBLEM DOES NOT EXIST");
		}
		
				
		# get the current language
		$language = $em->find("AppBundle\Entity\Language", $language_id);
		
		if(!$language){
			return $this->returnForbiddenResponse("Language with id ".$language_id." does not exist");
		}		
		
		# get the type of submission		
		$is_teaching = $grader->isTeaching($user, $problem->assignment->section);
		
		if(!$is_teaching){

			# make sure that the assignment is still open for submission
			if($problem->assignment->cutoff_time < new \DateTime("now")){
				return $this->returnForbiddenResponse("TOO LATE TO SUBMIT FOR THIS PROBLEM");
			}
			
			if($problem->assignment->start_time > new \DateTime("now")){
				return $this->returnForbiddenResponse("TOO EARLY TO SUBMIT FOR THIS PROBLEM");
			}
			
			# get the current team
			$team = $grader->getTeam($user, $problem->assignment);		
			if(!$team && !$is_teaching){
				return $this->returnForbiddenResponse("YOU ARE NOT ON A TEAM OR TEACHING FOR THIS ASSIGNMENT");
			}
			
			# make sure that you haven't submitted too many times yet
			$curr_attempts = $grader->getNumTotalAttempts($user, $problem);		
			if($problem->total_attempts > 0 && $curr_attempts >= $problem->total_attempts){
				return $this->returnForbiddenResponse("ALREADY REACHED MAX ATTEMPTS FOR PROBLEM AT ".$curr_attempts);
			}
		}
		
		# INITIALIZE THE SUBMISSION
		# create an entity for the current submission
		$submission = new Submission($problem, $team, $user);	

		# persist to the database to get the id
		$em->persist($submission);
		$em->flush();	
		
		# SETTING UP FOLDERS
		$sub_dir = $web_dir."compilation/submissions/".$submission->id."/";
		
		$student_code_dir = $sub_dir."student_code/";
		$compiled_code_dir = $sub_dir."compiled_code/";
		
		$flags_dir = $sub_dir."flags/";
		$custom_validator_dir = $sub_dir."custom_validator/";
		
		$run_log_dir = $sub_dir."run_logs/";
		$time_log_dir = $sub_dir."time_logs/";
		$diff_log_dir = $sub_dir."diff_logs/";
		
		$input_file_dir = $sub_dir."input_files/";
		$output_file_dir = $sub_dir."output_files/";
		$arg_file_dir = $sub_dir."arg_files/";
		
		$user_output_dir = $sub_dir."user_output/";		
		
		# create all of the folders
		# make the directory for the submission output
		shell_exec("mkdir -p ".$sub_dir);
		shell_exec("mkdir -p ".$student_code_dir);	
		shell_exec("mkdir -p ".$compiled_code_dir);	
		shell_exec("mkdir -p ".$flags_dir);	
		shell_exec("mkdir -p ".$custom_validator_dir);	
		shell_exec("mkdir -p ".$run_log_dir);	
		shell_exec("mkdir -p ".$time_log_dir);	
		shell_exec("mkdir -p ".$diff_log_dir);	
		shell_exec("mkdir -p ".$input_file_dir);	
		shell_exec("mkdir -p ".$output_file_dir);	
		shell_exec("mkdir -p ".$arg_file_dir);	
		shell_exec("mkdir -p ".$user_output_dir);
		
		# uploads directory 
		# get the submitted file path
		$uploads_dir = $web_dir."compilation/uploads/".$user->id."/".$problem->id."/";
		$submitted_file_path = $uploads_dir.$submitted_filename;
		
		# save the input/output files to a temp folder by deblobinating them	
		$testcaseGen = $generator->generateTestcaseFiles($problem, $input_file_dir, $arg_file_dir, $output_file_dir);

		if($testcaseGen){
			return $testcaseGen;
		}		
		
		# CUSTOM VALIDATOR
		# save the custom validator to the temp folder
		shell_exec("cp ".$web_dir."compilation/custom_validator/*.* ".$custom_validator_dir);
		
		if($problem->custom_validator){
				
			$validate_file = $problem->deblobinateCustomValidator();	
			
			// overwrite the custom_validate.cpp file
			$custom_validator_file = fopen($custom_validator_dir."custom_validate.cpp", "w");
			if(!$custom_validator_file){
				 return $this->returnForbiddenResponse("Unable to open custom validator file for writing - contact a system admin");
			}
			fwrite($custom_validator_file, $validate_file);
			fclose($custom_validator_file);
		}	
		
		
		# SUBMISSION COMPILATION
		# open the submitted file and prep for compilation
		$submitted_file = fopen($submitted_file_path, "r");
		if(!$submitted_file){
			return $this->returnForbiddenResponse("Unable to open submitted file: ".$submitted_file_path." - contact a system admin");
		}
		$submission->submitted_file = $submitted_file;
		$submission->filename = $submitted_filename;
		
		//fclose($submitted_file);
		
		# move the file into the proper directory
		shell_exec("mv ".$submitted_file_path." ".$student_code_dir."/");
		
		# query for the current filetype		
		$extension = pathinfo($submitted_file_path, PATHINFO_EXTENSION);
		
		$is_zipped = false;
		if($extension == "zip"){
			$is_zipped = true;
		};		
		
		# get the problem language entity from the problem and language
		# store the compilation options from the problem language
						
		# set the main class and package name
		$submission->main_class_name = $main_class;
		$submission->package_name = $package_name;
		
		
		/* CREATE THE DOCKER CONTAINER */
		// required fields
		$docker_options = "";
		$dockerOptGen = $generator->generateDockerOptions($docker_options, $language, $submitted_filename, $problem, $main_class, $package_name, $is_zipped, true);
		
		if($dockerOptGen){
			return $dockerOptGen;
		}
		
		# RUN THE DOCKER COMPILATION
		$docker_time_limit = intval(count($problem->testcases) * ceil(floatval($problem->time_limit)/1000.0)) + 8 + rand(1,4);

		$docker_script = $web_dir."compilation/dockercompiler.sh \"".$docker_options."\" ".$submission->id." ".$docker_time_limit;
		
		#return $this->returnForbiddenResponse($docker_script);
		
		$docker_output = shell_exec($docker_script);	
		
		$docker_log_file = fopen($flags_dir."docker_log", "w");
		if(!$docker_log_file){
			return $this->returnForbiddenResponse("Cannot open docker_script.log - contact a system admin");
		}
		fwrite($docker_log_file, $docker_output);
		fclose($docker_log_file);
		
		#return $this->returnForbiddenResponse($docker_output);
		
		# PARSE FOR SUBMISSION		
		$submissionGen = $generator->generateSubmission($submission, $problem);
		
		if($submissionGen){
			return $submissionGen;
		}
				
		# see if this new submission should be the accepted one
		$qb_accepted = $em->createQueryBuilder();
		$qb_accepted->select('s')
				->from('AppBundle\Entity\Submission', 's')
				->where('s.problem = ?1')
				->andWhere('s.team = ?2')
				->andWhere('s.is_accepted = true')
				->setParameter(1, $problem)
				->setParameter(2, $team);
				
		$acc_query = $qb_accepted->getQuery();
		$prev_accepted_sol = $acc_query->getOneOrNullResult();
		
		# determine if the new submission is the best one yet
		if($grader->isAcceptedSubmission($submission, $prev_accepted_sol, $correct_extra_testcase_count + $correct_testcase_count)){
			$submission->is_accepted = true;
			
			if($prev_accepted_sol){
				$prev_accepted_sol->is_accepted = false;
			}
		}	
		
		if($prev_accepted_sol){
			$em->persist($prev_accepted_sol);
		}
		
		# ZIP DIRECTORY FOR DATABASE		
		if(!chdir($sub_dir)){
			return $this->returnForbiddenResponse("Cannot switch directories - contact a system admin");
		}
		
		shell_exec("zip -r ".$sub_dir."log.zip *");
		
		if(!chdir($web_dir)){
			return $this->returnForbiddenResponse("Cannot switch directories - contact a system admin");
		}
		
		$zip_file = fopen($sub_dir."log.zip", "r");
		if(!$zip_file){
			return $this->returnForbiddenResponse("Cannot open log zip file for reading - contact a system admin");
		}
		$submission->log_directory = $zip_file;
		
		# REMOVE TEMPORARY FOLDERS
		shell_exec("rm -rf ".$sub_dir);
		shell_exec("rm -rf ".$uploads_dir);
		
		# update the submission entity
		$em->persist($submission);
		$em->flush();
		
		# RETURN THE URL OF THE RESULT
		$url = $this->generateUrl('problem_result', array('submission_id' => $submission->id));
		
		$response = new Response(json_encode([		
			'redirect_url' => $url,			
		]));
		
		$response->headers->set('Content-Type', 'application/json');
		$response->setStatusCode(Response::HTTP_OK);
	
		return $response;
	}
	
	/* name=generate */
	public function generateAction(Request $request) {
		# entity manager
		$em = $this->getDoctrine()->getManager();		
		
		# gets the gradel/symfony_project directory
		$web_dir = $this->get('kernel')->getProjectDir()."/";
			
		$generator = new Generator($em, $web_dir);		
		$grader = new Grader($em);
						
		# get the current user
		$user= $this->get('security.token_storage')->getToken()->getUser();
		
		if(!$user){
			return $this->returnForbiddenResponse("USER DOES NOT EXIST");
		}
		
		# VALIDATION		
		$postData = $request->request->all();
		
		$problem_id = $postData['problemId'];
		$language_id = $postData['languageId'];
		$submitted_filename = trim($postData['filename']);
		$main_class = trim($postData['mainclass']);
		$package_name = trim($postData['packagename']);
		
		# make sure all the required post params were passed
		if(!isset($problem_id) || !isset($language_id) || !isset($submitted_filename) || !isset($main_class) || !isset($package_name)){
			return $this->returnForbiddenResponse("NOT EVERY NECESSARY FIELD WAS PROVIDED");
		}		
				
		# get the current problem
		$problem = $em->find("AppBundle\Entity\Problem", $problem_id);
		if(!$problem){
			return $this->returnForbiddenResponse("PROBLEM DOES NOT EXIST");
		}		
				
		# get the current language
		$language = $em->find("AppBundle\Entity\Language", $language_id);
		
		if(!$language){
			return $this->returnForbiddenResponse("Language with id ".$language_id." does not exist");
		}		
		
		# INITIALIZE A SUBMISSION
		# create an entity for the current submission
		$submission = new Submission($problem, $team, $user);	

		# persist to the database to get the id
		$em->persist($submission);
		$em->flush();	
		
		# SETTING UP FOLDERS
		$sub_dir = $web_dir."compilation/submissions/".$submission->id."/";
		
		$student_code_dir = $sub_dir."student_code/";
		$compiled_code_dir = $sub_dir."compiled_code/";
		
		$flags_dir = $sub_dir."flags/";
		$custom_validator_dir = $sub_dir."custom_validator/";
		
		$run_log_dir = $sub_dir."run_logs/";
		$time_log_dir = $sub_dir."time_logs/";
		$diff_log_dir = $sub_dir."diff_logs/";
		
		$input_file_dir = $sub_dir."input_files/";
		$output_file_dir = $sub_dir."output_files/";
		$arg_file_dir = $sub_dir."arg_files/";
		
		$user_output_dir = $sub_dir."user_output/";		
		
		# create all of the folders
		# make the directory for the submission output
		shell_exec("mkdir -p ".$sub_dir);
		shell_exec("mkdir -p ".$student_code_dir);	
		shell_exec("mkdir -p ".$compiled_code_dir);	
		shell_exec("mkdir -p ".$flags_dir);	
		shell_exec("mkdir -p ".$custom_validator_dir);	
		shell_exec("mkdir -p ".$run_log_dir);	
		shell_exec("mkdir -p ".$time_log_dir);	
		shell_exec("mkdir -p ".$diff_log_dir);	
		shell_exec("mkdir -p ".$input_file_dir);	
		shell_exec("mkdir -p ".$output_file_dir);	
		shell_exec("mkdir -p ".$arg_file_dir);	
		shell_exec("mkdir -p ".$user_output_dir);
		
		# uploads directory 
		# get the submitted file path
		$uploads_dir = $web_dir."compilation/uploads/".$user->id."/".$problem->id."/";
		$submitted_file_path = $uploads_dir.$submitted_filename;
		
		# save the input/output files to a temp folder by deblobinating them	
		$testcaseGen = $generator->generateTestcaseFiles($problem, $input_file_dir, $arg_file_dir, $output_file_dir);

		if($testcaseGen){
			return $testcaseGen;
		}
				
		# SUBMISSION COMPILATION
		# open the submitted file and prep for compilation
		$submitted_file = fopen($submitted_file_path, "r");
		if(!$submitted_file){
			return $this->returnForbiddenResponse("Unable to open submitted file: ".$submitted_file_path." - contact a system admin");
		}
		$submission->submitted_file = $submitted_file;
		$submission->filename = $submitted_filename;
		
		# move the file into the proper directory
		shell_exec("mv ".$submitted_file_path." ".$student_code_dir."/");
		
		# query for the current filetype		
		$extension = pathinfo($submitted_file_path, PATHINFO_EXTENSION);
		
		$is_zipped = false;
		if($extension == "zip"){
			$is_zipped = true;
		};		
		
		# set the main class and package name
		$submission->main_class_name = $main_class;
		$submission->package_name = $package_name;
		
		
		/* CREATE THE DOCKER CONTAINER */
		// required fields
		$docker_options = "";
		$dockerOptGen = $generator->generateDockerOptions($docker_options, $language, $submitted_filename, $problem, $main_class, $package_name, $is_zipped, false);
		
		if($dockerOptGen){
			return $dockerOptGen;
		}
		
		# RUN THE DOCKER COMPILATION
		$docker_time_limit = intval(count($problem->testcases) * ceil(floatval($problem->time_limit)/1000.0)) + 8 + rand(1,4);

		$docker_script = $web_dir."compilation/dockercompiler.sh \"".$docker_options."\" ".$submission->id." ".$docker_time_limit;
		
		#return $this->returnForbiddenResponse($docker_script);
		
		$docker_output = shell_exec($docker_script);	
		
		$docker_log_file = fopen($flags_dir."docker_log", "w");
		if(!$docker_log_file){
			return $this->returnForbiddenResponse("Cannot open docker_script.log - contact a system admin");
		}
		fwrite($docker_log_file, $docker_output);
		fclose($docker_log_file);
		
		#return $this->returnForbiddenResponse($docker_output);
		
		# PARSE FOR SUBMISSION		
		$submissionGen = $generator->generateOutput($problem);
		
		if($submissionGen){
			return $submissionGen;
		}
				
		# REMOVE TEMPORARY FOLDERS
		shell_exec("rm -rf ".$sub_dir);
		shell_exec("rm -rf ".$uploads_dir);
		
		# RETURN THE URL OF THE RESULT
		$url = $this->generateUrl('problem_edit', [
			'problemId' => $problem->id,
			'assignmentId' => $problem->assignment->id,
			'sectionId' => $problem->assignment->section->id,
		]);
		
		$response = new Response(json_encode([		
			'redirect_url' => $url,			
		]));
		
		$response->headers->set('Content-Type', 'application/json');
		$response->setStatusCode(Response::HTTP_OK);
	
		return $response;
	}
	
	private function returnForbiddenResponse($message){		
		$response = new Response($message);
		$response->setStatusCode(Response::HTTP_FORBIDDEN);
		return $response;
	}
	
	
}

?>
