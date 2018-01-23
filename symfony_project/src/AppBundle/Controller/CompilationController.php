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
use AppBundle\Entity\Feedback;
use AppBundle\Entity\TestcaseResult;

use Symfony\Component\Config\Definition\Exception\Exception;

use AppBundle\Utils\Grader;
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
				
		# POST DATA
		$postData = $request->request->all();		
		$problem_id = $postData['problem_id'];
		
		# get the current problem
		if(!isset($problem_id) || !($problem_id > 0)){
			return $this->returnForbiddenResponse("PROBLEM ID WAS NOT PROVIDED PROPERLY");
		}
		
		$problem = $em->find("AppBundle\Entity\Problem", $problem_id);
		if(!$problem){
			return $this->returnForbiddenResponse("PROBLEM DOES NOT EXIST");
		}
				
		# get the type of submission		
		$is_teaching = ($grader->isTeaching($user, $problem->assignment->section) || $grader->isJudging($user, $problem->assignment->section));
		
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
			if(!$team && !$user->hasRole("ROLE_SUPER") && !$user->hasRole("ROLE_ADMIN")){
				return $this->returnForbiddenResponse("YOU ARE NOT ON A TEAM OR TEACHING FOR THIS ASSIGNMENT");
			}
			
			# make sure that you haven't submitted too many times yet
			$curr_attempts = $grader->getNumTotalAttempts($user, $problem);		
			if($problem->total_attempts > 0 && $curr_attempts >= $problem->total_attempts){
				return $this->returnForbiddenResponse("ALREADY REACHED MAX ATTEMPTS FOR PROBLEM AT ".$curr_attempts);
			}
		}
		
		$is_teaching = $is_teaching || $user->hasRole("ROLE_SUPER") || $user->hasRole("ROLE_ADMIN");
						
		# FILE UPLOAD
		# upload the file via the UploadController
		$response = $this->forward('AppBundle\Controller\UploadController::submitProblemUploadAction', array(
			'problem_id'  => $problem->id,
			'request' => $request,
		));		
		
		$content = (array) json_decode($response->getContent())->data;
		
		if(!(count($content) > 0)){
			return $response;
		}
				
		$submitted_filename = $content['submitted_filename'];
		$language_id = $content['language_id'];
		$main_class = $content['main_class'];
		$package_name = $content['package_name'];
		
		if(!isset($submitted_filename) || trim($submitted_filename) == ""){
			return $this->returnForbiddenResponse("Filename was not provided. Contact a systems administrator");
		}
			
		# make sure all the required post params were passed
		if(!isset($language_id) || !isset($main_class) || !isset($package_name)){
			return $this->returnForbiddenResponse("NOT EVERY NECESSARY FIELD WAS PROVIDED");
		}		
		
		# check main class and package name for validity
		if(strlen($main_class) > 0 && preg_match("/^[a-zA-Z0-9_]+$/", $main_class) != 1){
			return $this->returnForbiddenResponse("MAIN CLASS MUST BE ONLY LETTERS, NUMBERS, OR UNDERSCORES");
		}
		
		if(strlen($package_name) > 0 && preg_match("/^[a-zA-Z0-9_]+$/", $package_name) != 1){
			return $this->returnForbiddenResponse("PACKAGE NAME MUST BE ONLY LETTERS, NUMBERS, OR UNDERSCORES");
		}
		
		# check filename for validity
		if(preg_match("/^[a-zA-Z0-9_\-]+\.[a-zA-Z0-9]+$/", $submitted_filename) != 1){
			return $this->returnForbiddenResponse("SUBMITTED FILENAME IS NOT VALID");
		}
			
		# get the current language
		if(!($language_id > 0)){
			return $this->returnForbiddenResponse("LANGUAGE ID WAS NOT FORMATTED PROPERLY");
		}
		
		$language = $em->find("AppBundle\Entity\Language", $language_id);
		
		if(!$language){
			return $this->returnForbiddenResponse("Language with id ".$language_id." does not exist");
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

		if($testcaseGen != 1){
			
			$this->cleanUp($submission, null, $sub_dir, $uploads_dir);	
			return $this->returnForbiddenResponse($testcaseGen."");
		}		
		
		# CUSTOM VALIDATOR
		# save the custom validator to the temp folder
		shell_exec("cp ".$web_dir."compilation/custom_validator/*.* ".$custom_validator_dir);
		
		if($problem->custom_validator){
				
			$validate_file = $problem->deblobinateCustomValidator();	
			
			// overwrite the custom_validate.cpp file
			$custom_validator_file = fopen($custom_validator_dir."custom_validate.cpp", "w");
			if(!$custom_validator_file){				
				$this->cleanUp($submission, null, $sub_dir, $uploads_dir);		
				return $this->returnForbiddenResponse("Unable to open custom validator file for writing - contact a system admin");
			}
			fwrite($custom_validator_file, $validate_file);
			fclose($custom_validator_file);
		}	
		
		
		# SUBMISSION COMPILATION
		# open the submitted file and prep for compilation
		$submitted_file = fopen($submitted_file_path, "r");
		if(!$submitted_file){
			$this->cleanUp($submission, null, $sub_dir, $uploads_dir);	
			return $this->returnForbiddenResponse("Unable to open submitted file - if you weren't fooling around in the javascript, contact a system admin");
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
		$submission->language = $language;
		$submission->main_class_name = $main_class;
		$submission->package_name = $package_name;
		
		
		/* CREATE THE DOCKER CONTAINER */
		// required fields
		$docker_options = "";
		$dockerOptGen = $generator->generateDockerOptions($docker_options, 
															$language, 
															$submitted_filename, 
															$problem, 
															$main_class, 
															$package_name, 
															$is_zipped, 
															true);
		
		#return $this->returnForbiddenResponse($docker_options);
		
		if($dockerOptGen != 1){
			
			$this->cleanUp($submission, null, $sub_dir, $uploads_dir);		
			return $this->returnForbiddenResponse($dockerOptGen."");
		}
		
		# RUN THE DOCKER COMPILATION
		$docker_time_limit = intval(count($problem->testcases) * ceil(floatval($problem->time_limit)/1000.0)) + 8 + rand(1,4);

		$docker_script = $web_dir."compilation/dockercompiler.sh \"".$docker_options."\" ".$submission->id." ".$docker_time_limit;
		
		#return $this->returnForbiddenResponse($docker_script);
		
		$docker_output = shell_exec($docker_script);	
		
		//return $this->returnForbiddenResponse($docker_output);
		
		$docker_log_file = fopen($flags_dir."docker_log", "w");
		if(!$docker_log_file){
			$this->cleanUp($submission, null, $sub_dir, $uploads_dir);		
			return $this->returnForbiddenResponse("Cannot create docker_script.log - contact a system admin");
		}
		fwrite($docker_log_file, $docker_output);
		fclose($docker_log_file);
		
		#return $this->returnForbiddenResponse($docker_output);
		
		# PARSE FOR SUBMISSION		
		$submissionGen = $generator->generateSubmission($submission, $problem);
		
		if($submissionGen != 1){
			
			$this->cleanUp($submission, null, $sub_dir, $uploads_dir);		
			return $this->returnForbiddenResponse($submissionGen."");
		}
		
		# ZIP DIRECTORY FOR DATABASE		
		if(!chdir($sub_dir)){
			$this->cleanUp($submission, null, $sub_dir, $uploads_dir);	
			return $this->returnForbiddenResponse("Cannot switch directories - contact a system admin");
		}
		
		shell_exec("zip -r ".$sub_dir."log.zip *");
		
		if(!chdir($web_dir)){
			$this->cleanUp($submission, null, $sub_dir, $uploads_dir);	
			return $this->returnForbiddenResponse("Cannot switch directories - contact a system admin");
		}
		
		$zip_file = fopen($sub_dir."log.zip", "r");
		if(!$zip_file){
			$this->cleanUp($submission, null, $sub_dir, $uploads_dir);	
			return $this->returnForbiddenResponse("Cannot open log zip file for reading - contact a system admin");
		}
		$submission->log_directory = $zip_file;
		
		# REMOVE TEMPORARY FOLDERS
		$this->cleanUp(null, null, $sub_dir, $uploads_dir);
						
		# see if this new submission should be the accepted one
		if(!isset($team)){
			$whereClause = 's.user = ?2';
			$teamOrUser = $user;
		} else {
			$whereClause = 's.team = ?2';
			$teamOrUser = $team;
		}
		
		$qb_accepted = $em->createQueryBuilder();
		$qb_accepted->select('s')
			->from('AppBundle\Entity\Submission', 's')
			->where('s.problem = ?1')
			->andWhere($whereClause)
			->andWhere('s.is_accepted = true')
			->setParameter(1, $problem)
			->setParameter(2, $teamOrUser);
				
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
		
		$postData = $request->request->all();
		
		if(!isset($postData['assignmentId']) || !($postData['assignmentId'] > 0)){
			return $this->returnForbiddenResponse("Assignment ID was not provided or not formatted properly");
		}
		
		$assignment = $em->find("AppBundle\Entity\Assignment", $postData['assignmentId']);
		
		if(!$assignment){
			return $this->returnForbiddenResponse("Assignment does not exist");
		}
		
		$is_teaching = $grader->isTeaching($user, $assignment->section) || $grader->isJudging($user, $assignment->section);		
		if(!$is_teaching && !$user->hasRole("ROLE_SUPER") && !$user->hasRole("ROLE_ADMIN")){
			return $this->returnForbiddenResponse("You are not allowed to generate output for this problem");
		}
		
		
		# PROBLEM CREATION
		$problem = new Problem();
		
		$problem->name = "";
		$problem->description = "";
		$problem->weight = 1;
		$problem->time_limit = 10000;
		
		$problem->is_extra_credit = false;
		
		$problem->total_attempts = 0;
		$problem->attempts_before_penalty = 0;
		$problem->penalty_per_attempt = 0;
		
		$problem->stop_on_first_fail = false;
		$problem->response_level = "";
		$problem->display_testcaseresults = true;
		$problem->testcase_output_level = "";
		$problem->extra_testcases_display = true;
		
		$em->persist($problem);
		
		# instantiate the languages
		$qb = $em->createQueryBuilder();
		$qb->select('l')
			->from('AppBundle\Entity\Language', 'l')
			->where('1 = 1');
		$languages = $qb->getQuery()->getResult();
		
		foreach($languages as $language){

			$problemLanguage = new ProblemLanguage();

			$problemLanguage->language = $language;
			$problemLanguage->problem = $problem;
			$em->persist($problemLanguage);
		}		
		
		# TESTCASES
		$postData = $request->request->all();
		$postTestcases = (array) json_decode($postData['testcases']);
		
		if(!(count($postTestcases) >= 1)){
			return $this->returnForbiddenResponse("No testcases given!");
		}
		
		$count = 1;
		foreach($postTestcases as &$tc){
			
			$tc = (array) $tc;
			
			# build the testcase
			try{				
				$testcase = new Testcase($problem, $tc, $count);
				$count++;
					
				$em->persist($testcase);
				$problem->testcases[] = $testcase;	
			} catch(Exception $e){
				return $this->returnForbiddenResponse($e->getMessage());
			}
		}		
		
		$em->persist($problem);		
		$em->flush();
							
		# FILE UPLOAD
		# upload the file via the UploadController
		$response = $this->forward('AppBundle\Controller\UploadController::submitProblemUploadAction', array(
			'problem_id'  => $problem->id,
			'request' => $request,
		));		
		
		$content = (array) json_decode($response->getContent())->data;	

		if(!isset($content)){
			return $response;
		}		
		
		$submitted_filename = $content['submitted_filename'];
		$language_id = $content['language_id'];
		$main_class = $content['main_class'];
		$package_name = $content['package_name'];
		
		if(!isset($submitted_filename) || trim($submitted_filename) == ""){
			return $this->returnForbiddenResponse("Filename was not provided. Contact a systems administrator");
		}

		# INITIALIZE A SUBMISSION
		# create an entity for the current submission
		$submission = new Submission($problem, null, null);	

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
		
		//return $this->returnForbiddenResponse(var_dump($submission->id));
		//return $this->returnForbiddenResponse(var_dump($testcaseGen));
		if($testcaseGen != 1){			
			$this->cleanUp($submission, $problem, $sub_dir, $uploads_dir);	
			return $this->returnForbiddenResponse($testcaseGen."");
		}
				
		# SUBMISSION COMPILATION
		# open the submitted file and prep for compilation
		$submitted_file = fopen($submitted_file_path, "r");
		if(!$submitted_file){
			$this->cleanUp($submission, $problem, $sub_dir, $uploads_dir);
			return $this->returnForbiddenResponse("Unable to open submitted file: ".$submitted_file_path." - contact a system admin");
		}
		$submission->language = $language;
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
				
		# get the current language
		if(!isset($language_id) || !($language_id > 0)){
			return $this->returnForbiddenResponse("PROBLEM ID WAS NOT PROVIDED PROPERLY");
		}
		
		$language = $em->find("AppBundle\Entity\Language", $language_id);
		//return null;
		if(!$language){
			$this->cleanUp($submission, $problem, $sub_dir, $uploads_dir);	
			return $this->returnForbiddenResponse("Language with id ".$language_id." does not exist");
		}				
		
		/* CREATE THE DOCKER CONTAINER */
		// required fields
		$docker_options = "";
		$dockerOptGen = $generator->generateDockerOptions($docker_options, $language, $submitted_filename, $problem, $main_class, $package_name, $is_zipped, false);
		
		if($dockerOptGen != 1){			
			$this->cleanUp($submission, $problem, $sub_dir, $uploads_dir);	
			return $this->returnForbiddenResponse($dockerOptGen."");
		}
		
		# RUN THE DOCKER COMPILATION
		$docker_time_limit = intval(count($problem->testcases) * ceil(floatval($problem->time_limit)/1000.0)) + 8 + rand(1,4);

		$docker_script = $web_dir."compilation/dockercompiler.sh \"".$docker_options."\" ".$submission->id." ".$docker_time_limit;
		
		#return $this->returnForbiddenResponse($docker_script);
		
		$docker_output = shell_exec($docker_script);	
		
		$docker_log_file = fopen($flags_dir."docker_log", "w");
		if(!$docker_log_file){
			$this->cleanUp($submission, $problem, $sub_dir, $uploads_dir);
			return $this->returnForbiddenResponse("Cannot open docker_script.log - contact a system admin");
		}
		fwrite($docker_log_file, $docker_output);
		fclose($docker_log_file);
		
		#return $this->returnForbiddenResponse($docker_output);
		
		# PARSE FOR SUBMISSION		
		$testcases = [];
		$submissionGen = $generator->generateOutput($testcases, $submission, count($problem->testcases));
			
		if($submissionGen != 1) {
			$this->cleanUp($submission, $problem, $sub_dir, $uploads_dir);
			return $this->returnForbiddenResponse($submissionGen."");
		}
						
		# REMOVE TEMPORARY FOLDERS AND DATABASES
		$this->cleanUp($submission, $problem, $sub_dir, $uploads_dir);
		
		# RETURN THE TESTCASES OF THE RESULT
		$response = new Response(json_encode([		
			'testcases' => $testcases,			
		]));
		
		$response->headers->set('Content-Type', 'application/json');
		$response->setStatusCode(Response::HTTP_OK);
	
		return $response;
	}
	
	// function to remove the submission and problem on failure
	private function cleanUp($submission, $problem, $sub_dir, $uploads_dir){
		
		# entity manager
		$em = $this->getDoctrine()->getManager();	
		
		if(isset($submission)){
			$em->remove($submission);
		}
		
		if(isset($problem)){
			$em->remove($problem);
		}
		
		if(isset($sub_dir)){
			shell_exec("rm -rf ".$sub_dir);
		}
		
		if(isset($uploads_dir)){
			shell_exec("rm -rf ".$uploads_dir);			
		}
		
		$em->flush();
	}
	
	private function returnForbiddenResponse($message){		
		$response = new Response($message);
		$response->setStatusCode(Response::HTTP_FORBIDDEN);
		return $response;
	}
	
	
}

?>
