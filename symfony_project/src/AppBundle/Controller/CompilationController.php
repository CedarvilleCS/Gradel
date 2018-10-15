<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Assignment;
use AppBundle\Entity\Course;
use AppBundle\Entity\Feedback;
use AppBundle\Entity\Language;
use AppBundle\Entity\Problem;
use AppBundle\Entity\ProblemLanguage;
use AppBundle\Entity\Role;
use AppBundle\Entity\Section;
use AppBundle\Entity\Submission;
use AppBundle\Entity\Team;
use AppBundle\Entity\Testcase;
use AppBundle\Entity\TestcaseResult;
use AppBundle\Entity\Trial;
use AppBundle\Entity\User;
use AppBundle\Entity\UserSectionRole;

use Symfony\Component\Config\Definition\Exception\Exception;

use AppBundle\Utils\Generator;
use AppBundle\Utils\Grader;
use AppBundle\Utils\SocketPusher;
use AppBundle\Utils\Uploader;
use AppBundle\Utils\Zipper;

use \DateTime;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Psr\Log\LoggerInterface;

class CompilationController extends Controller {	
	private $logger;

	public function __construct(LoggerInterface $logger) {
		$this->logger = $logger;
	}
	
	/* submit */
	public function submitAction(Request $request, $trialId=0, $forwarded="") {
				
		# entity manager
		$em = $this->getDoctrine()->getManager();		
		
		# gets the gradel/symfony_project directory
		$web_dir = $this->get('kernel')->getProjectDir()."/";
			
			
		$uploader = new Uploader($web_dir);
		$generator = new Generator($em, $web_dir);		
		$grader = new Grader($em);
						
		# get the current user
		$user= $this->get('security.token_storage')->getToken()->getUser();
		
		if(!$user){
			return $this->returnForbiddenResponse("USER DOES NOT EXIST");
		}
				
		# POST DATA
		$postData = $request->request->all();
		$trial_id = $postData['trial_id'];
		
		if($trial_id == null){
			$trial_id = $trialId;
		}
		
		# get the current trial
		$trial = $em->find("AppBundle\Entity\Trial", $trial_id);
		
		if(!$trial || $trial->user != $user){
			return $this->returnForbiddenResponse("TRIAL DOES NOT EXIST");
		}
		
		$problem = $trial->problem;

		if($problem->assignment->section->course->is_contest && $forwarded != "secret_code"){
			return $this->returnForbiddenResponse("You are not allow to run this controller");
		}
	
		# validation
		$elevatedUser = ($grader->isTeaching($user, $problem->assignment->section) || $grader->isJudging($user, $problem->assignment->section) || $user->hasRole("ROLE_SUPER") || $user->hasRole("ROLE_ADMIN"));
		
		# get the type of submission				
		$team = null;
		
		// if you are taking, we need to get the team and the number of attempts
		if( $grader->isTaking($user, $problem->assignment->section) ){

			# get the current team
			$team = $grader->getTeam($user, $problem->assignment);		
			if( !($team || $elevatedUser) ){
				return $this->returnForbiddenResponse("YOU ARE NOT ON A TEAM OR TEACHING FOR THIS ASSIGNMENT");
			}
		
			# make sure that the assignment is still open for submission
			if(!$elevatedUser && $problem->assignment->cutoff_time < new \DateTime("now")){
				return $this->returnForbiddenResponse("TOO LATE TO SUBMIT FOR THIS PROBLEM");
			}
			
			if(!$elevatedUser && $problem->assignment->start_time > new \DateTime("now")){
				return $this->returnForbiddenResponse("TOO EARLY TO SUBMIT FOR THIS PROBLEM");
			}
			
			# make sure that you haven't submitted too many times yet
			$curr_attempts = $grader->getNumTotalAttempts($user, $problem);		
			if(!$elevatedUser && $problem->total_attempts > 0 && $curr_attempts >= $problem->total_attempts){
				return $this->returnForbiddenResponse("ALREADY REACHED MAX ATTEMPTS FOR PROBLEM AT ".$curr_attempts." ATTEMPTS");
			}
		} else if( !$elevatedUser ){
			
			return $this->returnForbiddenResponse("YOU ARE NOT PERMITTED TO SUBMIT FOR THIS PROBLEM");
			
		}
		
		$submitted_filename = $uploader->createSubmissionFile($trial);
		
		$main_class = $trial->main_class;
		$package_name = $trial->package_name;
		$language = $trial->language;
		
		if(!isset($submitted_filename) || trim($submitted_filename) == ""){
			return $this->returnForbiddenResponse("UNABLE TO CREATE FILE");
		}
		
		# check main class and package name for validity
		if(strlen($main_class) > 0 && preg_match("/^[a-zA-Z0-9_]+$/", $main_class) != 1){
			return $this->returnForbiddenResponse("MAIN CLASS MUST BE ONLY LETTERS, NUMBERS, OR UNDERSCORES");
		}
		
		if(strlen($package_name) > 0 && preg_match("/^[a-zA-Z0-9_]+$/", $package_name) != 1){
			return $this->returnForbiddenResponse("PACKAGE NAME MUST BE ONLY LETTERS, NUMBERS, OR UNDERSCORES");
		}

		# INITIALIZE THE SUBMISSION
		# create an entity for the current submission from the trial
		$submission = new Submission($trial, $team);	

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
		# move the submitted file into the proper directory
		shell_exec("mv ".$submitted_file_path." ".$student_code_dir."/");
		
		# the submission is always zipped now
		$is_zipped = true;	
		
		# get the problem language entity from the problem and language
		# store the compilation options from the problem language
		
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
				
		if($dockerOptGen != 1){
			
			$this->cleanUp($submission, null, $sub_dir, $uploads_dir);		
			return $this->returnForbiddenResponse($dockerOptGen."");
		}
		
		# RUN THE DOCKER COMPILATION
		$docker_time_limit = intval(count($problem->testcases) * ceil(floatval($problem->time_limit)/1000.0)) + 120;

		$docker_script = $web_dir."compilation/dockercompiler.sh \"".$docker_options."\" ".$submission->id." ".$docker_time_limit;
		$docker_output = shell_exec($docker_script);	
		
		$docker_log_file = fopen($flags_dir."docker_log", "w");
		if(!$docker_log_file){
			$this->cleanUp($submission, null, $sub_dir, $uploads_dir);		
			return $this->returnForbiddenResponse("Cannot create docker_script.log - contact a system admin");
		}
		fwrite($docker_log_file, $docker_output);
		fclose($docker_log_file);
				
		# PARSE FOR SUBMISSION		
		$solvedAllTestcases = false;
		$submissionGen = $generator->generateSubmission($submission, $problem, $solvedAllTestcases);
		
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
			->andWhere('s.best_submission = true')
			->setParameter(1, $problem)
			->setParameter(2, $teamOrUser)
			->orderBy('s.timestamp', 'DESC');
				
		$acc_query = $qb_accepted->getQuery();
		$prev_accepted_sol = $acc_query->getResult()[0];
		
		# determine if the new submission is the best one yet
		if($grader->isAcceptedSubmission($submission, $prev_accepted_sol)){
			$submission->best_submission = true;
			
			if($prev_accepted_sol){
				$prev_accepted_sol->best_submission = false;
				$em->persist($prev_accepted_sol);
			}
		}
		// update pending status
		$submission->pending_status = 2;

		# complete the submission
		$submission->is_completed = true;
		
		# update the submission entity
		$em->persist($submission);
		$em->flush();

	
		$url = $this->generateUrl('problem_result', [
			'submission_id' => $submission->id
		]);
	
		$response = new Response(json_encode([		
			'redirect_url' => $url,	
			'submission_id' => $submission->id,		
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
		$uploader = new Uploader($web_dir);		
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
		
		$elevatedUser = $user->hasRole("ROLE_SUPER") || $user->hasRole("ROLE_ADMIN") || $grader->isJudging($user, $assignment->section) || $grader->isTeaching($user, $assignment->section);		
		if( !($elevatedUser || ($grader->isTaking($user, $assignment->section) && $assignment->isActive())) ){
			return $this->returnForbiddenResponse("PERMISSION DENIED");
		}
		
		# PROBLEM CREATION
		$problem = new Problem();
		
		$problem->name = "";
		$problem->description = "";
		$problem->weight = 1;
		$problem->time_limit = 1000;
		
		$problem->is_extra_credit = false;
		
		$problem->total_attempts = 0;
		$problem->attempts_before_penalty = 0;
		$problem->penalty_per_attempt = 0;
		
		$problem->stop_on_first_fail = false;
		$problem->response_level = "";
		$problem->display_testcaseresults = true;
		$problem->testcase_output_level = "";
		$problem->extra_testcases_display = true;
		$problem->allow_multiple = true;
		$problem->allow_upload = true;
		
		$em->persist($problem);
		
		# instantiate the problem languages
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
				
				if(!isset($testcase->input) || trim($testcase->input) == ""){
					//return $this->returnForbiddenResponse(json_encode($testcase));
					return $this->returnForbiddenResponse("Your testcases are not valid");
				}
				
				$count++;
					
				$em->persist($testcase);
				$problem->testcases[] = $testcase;	
			} catch(Exception $e){
				return $this->returnForbiddenResponse($e->getMessage());
			}
		}		
		
		$em->persist($problem);		
		$em->flush();
							
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
		
		# GENERATE THE FILES
		$aceData = json_decode($postData['ACE']);
		
		// make a temporary directory
		$tempdir = null;
		
		while(!is_dir($tempdir)){
			
			$tempdir = tempnam(sys_get_temp_dir(),'');
			
			if (file_exists($tempdir)){
				unlink($tempdir);
			}
			mkdir($tempdir);
		}
		$tempdir .= '/';
				
		$total_size = 0;
		
		$array_of_names = [];		
		foreach($aceData as $aceDatum){
			
			if(strlen($aceDatum->content) <= 0){
				return $this->returnForbiddenResponse('Your file cannot be empty');
			}
			
			if(strlen($aceDatum->filename) <= 0){
				return $this->returnForbiddenResponse('Your filename cannot be blank');
			}
			
			if(preg_match('/^[a-zA-Z0-9-_]+\.[a-zA-Z]+$/', $aceDatum->filename) <= 0){
				return $this->returnForbiddenResponse('Your filename is invalid');
			}
			
			$aceContent = $aceDatum->content;
			$filename = $aceDatum->filename;

			
			$total_size += strlen($aceContent);
		
			if($total_size > 1024*1024){
				return $this->returnForbiddenResponse("Uploaded code must be smaller than 1Mb total.");
			}
			
			if(!file_put_contents($tempdir.$filename, $aceContent, FILE_USE_INCLUDE_PATH)){
				 return $this->returnForbiddenResponse("UNABLE TO MOVE THE ACE EDITOR CONTENTS");
			}			
		}
		
		$zipper = new Zipper();
		$target_file = $tempdir."zippy.zip";
		
		$response = $zipper->zipFiles($tempdir, $target_file);
			
		if($response !== TRUE){
			return $this->returnForbiddenResponse($response."");
		}		
		
		// make a zip file and set file = fopen(zip location)
		$file = fopen($target_file, 'r');		
		if(!$file){
			return $this->returnForbiddenResponse("Could not properly open file for moving.");
		}
		
		# uploads directory 
		# get the submitted file path
		$uploads_dir = $web_dir."compilation/uploads/".$user->id."/".$problem->id."/";
		$submitted_file_path = $uploads_dir."zippy.zip";

		# PUT THE ZIP FILE IN THE UPLOADS DIRECTORY
		$submitted_filename = $uploader->createGeneratorFile($user, $problem, "zippy.zip", $file);
		//return $this->returnForbiddenResponse(json_encode($submitted_filename));
		if(!$submitted_filename){
			$this->cleanUp($submission, $problem, $sub_dir, $uploads_dir);	
			return $this->returnForbiddenResponse("Unable to create file for submission");
		}

		// move into docker area
		shell_exec("mv ".$submitted_file_path." ".$student_code_dir."/");

			# save the input/output files to a temp folder by deblobinating them	
		$testcaseGen = $generator->generateTestcaseFiles($problem, $input_file_dir, $arg_file_dir, $output_file_dir);
		if($testcaseGen != 1){			
			$this->cleanUp($submission, $problem, $sub_dir, $uploads_dir);	
			return $this->returnForbiddenResponse($testcaseGen."");
		}
			
		# get the current language
		$language_id = $postData['language'];
		if(!isset($language_id) || !($language_id > 0)){
			$this->cleanUp($submission, $problem, $sub_dir, $uploads_dir);	
			return $this->returnForbiddenResponse("PROBLEM ID WAS NOT PROVIDED PROPERLY");
		}
		
		$language = $em->find("AppBundle\Entity\Language", $language_id);
		if(!$language){
			$this->cleanUp($submission, $problem, $sub_dir, $uploads_dir);	
			return $this->returnForbiddenResponse("Language with id ".$language_id." does not exist");
		}		
		
		# set the main class and package name
		$main_class = $postData['main_class'];
		$package_name = $postData['package_name'];
				
		# check main class and package name for validity
		if(!isset($main_class) || strlen($main_class) > 0 && preg_match("/^[a-zA-Z0-9_]+$/", $main_class) != 1){
			return $this->returnForbiddenResponse("MAIN CLASS MUST BE ONLY LETTERS, NUMBERS, OR UNDERSCORES");
		}
		
		if(!isset($package_name) || strlen($package_name) > 0 && preg_match("/^[a-zA-Z0-9_]+$/", $package_name) != 1){
			return $this->returnForbiddenResponse("PACKAGE NAME MUST BE ONLY LETTERS, NUMBERS, OR UNDERSCORES");
		}

		$submission->main_class_name = $main_class;
		$submission->package_name = $package_name;
		$submission->language = $language;
		$submission->submitted_file = "unimportant";
		$submission->filename = $submitted_filename;

		$is_zipped = true;
		
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
															false);
		
		if($dockerOptGen != 1){			
			$this->cleanUp($submission, $problem, $sub_dir, $uploads_dir);	
			return $this->returnForbiddenResponse($dockerOptGen."");
		}
		
		# RUN THE DOCKER COMPILATION
		$docker_time_limit = intval(count($problem->testcases) * ceil(floatval($problem->time_limit)/1000.0)) + 40;

		$docker_script = $web_dir."compilation/dockercompiler.sh \"".$docker_options."\" ".$submission->id." ".$docker_time_limit;
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
		//	$this->cleanUp($submission, $problem, $sub_dir, $uploads_dir);
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
			//shell_exec("rm -rf ".$sub_dir);
		}
		
		if(isset($uploads_dir)){
			shell_exec("rm -rf ".$uploads_dir);			
		}
		
		$em->flush();
	}

	private function logError($message) {
		$errorMessage = "AssignmentController: ".$message;
		$this->logger->error($errorMessage);
		return $errorMessage;
	}
	
	private function returnForbiddenResponse($message){		
		$response = new Response($message);
		$response->setStatusCode(Response::HTTP_FORBIDDEN);
		$this->logError($message);
		return $response;
	}

	private function returnOkResponse($response) {
		$response->headers->set("Content-Type", "application/json");
		$response->setStatusCode(Response::HTTP_OK);
		return $response;
	}
}

?>
