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

use AppBundle\Service\SubmissionService;
use AppBundle\Service\TrialService;
use AppBundle\Service\UserService;

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
	private $submissionService;
	private $trialService;
	private $userService;

	public function __construct(LoggerInterface $logger,
	                            SubmissionService $submissionService,
	                            TrialService $trialService,
	                            UserService $userService) {
		$this->logger = $logger;
		$this->submissionService = $submissionService;
		$this->trialService = $trialService;
		$this->userService = $userService;
	}
	
	/* submit */
	public function submitAction(Request $request, $trialId = 0, $forwarded = "") {
		/* entity manager */
		$entityManager = $this->getDoctrine()->getManager();
		
		/* gets the gradel/symfony_project directory */
		$webDirectory = $this->get("kernel")->getProjectDir()."/";

		$generator = new Generator($entityManager, $webDirectory);		
		$grader = new Grader($entityManager);
		$uploader = new Uploader($webDirectory);
						
		/* get the current user */
		$user = $this->userService->getCurrentUser($entityManager);
		
		if (!$user) {
			return $this->returnForbiddenResponse("USER DOES NOT EXIST");
		}
				
		/* POST DATA */
		$postData = $request->request->all();
		
		if ($postData["trial_id"] != null) {
			$trialId = $postData["trial_id"];
		}
		
		/* get the current trial */
		$trial = $this->trialService->getTrialById($entityManager, $trialId);
		
		if (!$trial || $trial->user != $user) {
			return $this->returnForbiddenResponse("TRIAL DOES NOT EXIST");
		}
		
		$problem = $trial->problem;

		if ($problem->assignment->section->course->is_contest && $forwarded != "secret_code") {
			return $this->returnForbiddenResponse("You are not allow to run this controller");
		}
	
		/* validation */
		$elevatedUser = ($grader->isTeaching($user, $problem->assignment->section) || $grader->isJudging($user, $problem->assignment->section) || $user->hasRole("ROLE_SUPER") || $user->hasRole("ROLE_ADMIN"));
		
		/* get the type of submission */
		$team = null;
		
		/* if you are taking, we need to get the team and the number of attempts */
		if ($grader->isTaking($user, $problem->assignment->section)) {
			/* get the current team */
			$team = $grader->getTeam($user, $problem->assignment);		
			if (!($team || $elevatedUser)) {
				return $this->returnForbiddenResponse("YOU ARE NOT ON A TEAM OR TEACHING FOR THIS ASSIGNMENT");
			}
		
			/* make sure that the assignment is still open for submission */
			if (!$elevatedUser && $problem->assignment->cutoff_time < new \DateTime("now")) {
				return $this->returnForbiddenResponse("TOO LATE TO SUBMIT FOR THIS PROBLEM");
			}
			
			if (!$elevatedUser && $problem->assignment->start_time > new \DateTime("now")) {
				return $this->returnForbiddenResponse("TOO EARLY TO SUBMIT FOR THIS PROBLEM");
			}
			
			/* make sure that you haven"t submitted too many times yet */
			$currentAttempts = $grader->getNumTotalAttempts($user, $problem);
			if (!$elevatedUser && $problem->total_attempts > 0 && $currentAttempts >= $problem->total_attempts) {
				return $this->returnForbiddenResponse("ALREADY REACHED MAX ATTEMPTS FOR PROBLEM AT ".$currentAttempts." ATTEMPTS");
			}
		} else if (!$elevatedUser) {
			return $this->returnForbiddenResponse("YOU ARE NOT PERMITTED TO SUBMIT FOR THIS PROBLEM");
		}
		
		$submittedFilename = $uploader->createSubmissionFile($trial);
		
		$mainClass = $trial->main_class;
		$packageName = $trial->package_name;
		$language = $trial->language;
		
		if (!isset($submittedFilename) || trim($submittedFilename) == "") {
			return $this->returnForbiddenResponse("UNABLE TO CREATE FILE");
		}
		
		/* check main class and package name for validity */
		if (strlen($mainClass) > 0 && preg_match("/^[a-zA-Z0-9_]+$/", $mainClass) != 1) {
			return $this->returnForbiddenResponse("MAIN CLASS MUST BE ONLY LETTERS, NUMBERS, OR UNDERSCORES");
		}
		
		if (strlen($packageName) > 0 && preg_match("/^[a-zA-Z0-9_]+$/", $packageName) != 1) {
			return $this->returnForbiddenResponse("PACKAGE NAME MUST BE ONLY LETTERS, NUMBERS, OR UNDERSCORES");
		}

		/* INITIALIZE THE SUBMISSION */
		/* create an entity for the current submission from the trial */
		$submission = $this->submissionService->createSubmissionFromTrialAndTeamForCompilationSubmit($entityManager, $trial, $team);
		
		/* SETTING UP FOLDERS */
		$subDirectory = $webDirectory."compilation/submissions/".$submission->id."/";
		
		$studentCodeDirectory = $subDirectory."student_code/";
		$compiledCodeDirectory = $subDirectory."compiled_code/";
		
		$flagsDirectory = $subDirectory."flags/";
		$customValidatorDirectory = $subDirectory."custom_validator/";
		
		$runLogDirectory = $subDirectory."run_logs/";
		$timeLogDirectory = $subDirectory."time_logs/";
		$diffLogDirectory = $subDirectory."diff_logs/";
		
		$inputFileDirectory = $subDirectory."input_files/";
		$outputFileDirectory = $subDirectory."output_files/";
		$argFileDirectory = $subDirectory."arg_files/";
		
		$userOutputDirectory = $subDirectory."user_output/";
		
		/* create all of the folders */
		/* make the directory for the submission output */
		shell_exec("mkdir -p ".$subDirectory);
		shell_exec("mkdir -p ".$studentCodeDirectory);
		shell_exec("mkdir -p ".$compiledCodeDirectory);
		shell_exec("mkdir -p ".$flagsDirectory);
		shell_exec("mkdir -p ".$customValidatorDirectory);
		shell_exec("mkdir -p ".$runLogDirectory);
		shell_exec("mkdir -p ".$timeLogDirectory);
		shell_exec("mkdir -p ".$diffLogDirectory);
		shell_exec("mkdir -p ".$inputFileDirectory);
		shell_exec("mkdir -p ".$outputFileDirectory);
		shell_exec("mkdir -p ".$argFileDirectory);
		shell_exec("mkdir -p ".$userOutputDirectory);
		
		/* uploads directory */
		/* get the submitted file path */
		$uploadsDirectory = $webDirectory."compilation/uploads/".$user->id."/".$problem->id."/";
		$submittedFilePath = $uploadsDirectory.$submittedFilename;
		
		/* save the input/output files to a temp folder by deblobinating them */
		$testCaseGeneratorResult = $generator->generateTestcaseFiles($problem, $inputFileDirectory, $argFileDirectory, $outputFileDirectory);

		if ($testCaseGeneratorResult != 1) {
			$this->cleanUp($submission, null, $subDirectory, $uploadsDirectory);
			return $this->returnForbiddenResponse($testCaseGeneratorResult."");
		}

		/* CUSTOM VALIDATOR */
		/* save the custom validator to the temp folder */
		shell_exec("cp ".$webDirectory."compilation/custom_validator/*.* ".$customValidatorDirectory);
		
		if ($problem->custom_validator) {
			$validateFile = $problem->deblobinateCustomValidator();	
			
			/* overwrite the custom_validate.cpp file */
			$customValidatorFile = fopen($customValidatorDirectory."custom_validate.cpp", "w");
			if (!$customValidatorFile) {
				$this->cleanUp($submission, null, $subDirectory, $uploadsDirectory);
				return $this->returnForbiddenResponse("Unable to open custom validator file for writing - contact a system admin");
			}
			fwrite($customValidatorFile, $validateFile);
			fclose($customValidatorFile);
		}


		/* SUBMISSION COMPILATION */
		/* move the submitted file into the proper directory */
		shell_exec("mv ".$submittedFilePath." ".$studentCodeDirectory."/");
		
		/* the submission is always zipped now */
		$isZipped = true;
		
		/* get the problem language entity from the problem and language */
		/* store the compilation options from the problem language */
		
		/* CREATE THE DOCKER CONTAINER */
		/* required fields */
		$dockerOptions = "";
		$dockerOptionsGeneratorResult = $generator->generateDockerOptions($dockerOptions, 
		                                                                  $language, 
		                                                                  $submittedFilename,
		                                                                  $problem, 
		                                                                  $mainClass, 
		                                                                  $packageName, 
		                                                                  $isZipped, 
		                                                                  true);
				
		if ($dockerOptionsGeneratorResult != 1) {
			$this->cleanUp($submission, null, $subDirectory, $uploadsDirectory);		
			return $this->returnForbiddenResponse($dockerOptionsGeneratorResult."");
		}
		
		/* RUN THE DOCKER COMPILATION */
		$dockerTimeLimit = intval(count($problem->testcases) * ceil(floatval($problem->time_limit)/1000.0)) + 120;

		$dockerScript = $webDirectory."compilation/dockercompiler.sh \"".$dockerOptions."\" ".$submission->id." ".$dockerTimeLimit;
		$dockerOutput = shell_exec($dockerScript);
		
		$dockerLogFile = fopen($flagsDirectory."docker_log", "w");
		if (!$dockerLogFile) {
			$this->cleanUp($submission, null, $subDirectory, $uploadsDirectory);		
			return $this->returnForbiddenResponse("Cannot create docker_script.log - contact a system admin");
		}
		fwrite($dockerLogFile, $dockerOutput);
		fclose($dockerLogFile);

		/* PARSE FOR SUBMISSION	*/
		$solvedAllTestcases = false;
		$submissionGeneratorResult = $generator->generateSubmission($submission, $problem, $solvedAllTestcases);

		if ($submissionGeneratorResult != 1) {
			$this->cleanUp($submission, null, $subDirectory, $uploadsDirectory);		
			return $this->returnForbiddenResponse($submissionGeneratorResult."");
		}
		
		/* ZIP DIRECTORY FOR DATABASE */
		if (!chdir($subDirectory)) {
			$this->cleanUp($submission, null, $subDirectory, $uploadsDirectory);	
			return $this->returnForbiddenResponse("Cannot switch directories - contact a system admin");
		}
		
		shell_exec("zip -r ".$subDirectory."log.zip *");
		
		if (!chdir($webDirectory)) {
			$this->cleanUp($submission, null, $subDirectory, $uploadsDirectory);	
			return $this->returnForbiddenResponse("Cannot switch directories - contact a system admin");
		}
		
		$zipFile = fopen($subDirectory."log.zip", "r");
		if (!$zipFile) {
			$this->cleanUp($submission, null, $subDirectory, $uploadsDirectory);	
			return $this->returnForbiddenResponse("Cannot open log zip file for reading - contact a system admin");
		}
		$submission->log_directory = $zipFile;
		
		/* REMOVE TEMPORARY FOLDERS */
		$this->cleanUp(null, null, $subDirectory, $uploadsDirectory);
						
		/* see if this new submission should be the accepted one */
		if (!isset($team)) {
			$whereClause = "s.user = ?2";
			$teamOrUser = $user;
		} else {
			$whereClause = "s.team = ?2";
			$teamOrUser = $team;
		}

		$previousAcceptedSolution = $this->submissionService->getPreviousAcceptedSolutionForCompilationSubmit($entityManager, $teamOrUser, $whereClause, $problem);
		
		/* determine if the new submission is the best one yet */
		if ($grader->isAcceptedSubmission($submission, $previousAcceptedSolution)) {
			$submission->best_submission = true;
			
			if ($previousAcceptedSolution) {
				$previousAcceptedSolution->best_submission = false;
				$entityManager->persist($previousAcceptedSolution);
			}
		}
		
		/* update pending status */
		$submission->pending_status = 2;

		/* complete the submission */
		$submission->is_completed = true;
		
		/* update the submission entity */
		$entityManager->persist($submission);
		$entityManager->flush();
	
		$url = $this->generateUrl("problem_result", [
			"submission_id" => $submission->id
		]);
	
		$response = new Response(json_encode([		
			"redirect_url" => $url,	
			"submission_id" => $submission->id
		]));

		return $this->returnOkResponse($response);
	}
	
	/* name=generate */
	public function generateAction(Request $request) {
		
		# entity manager
		$entityManager = $this->getDoctrine()->getManager();		
		
		# gets the gradel/symfony_project directory
		$webDirectory = $this->get("kernel")->getProjectDir()."/";
			
		$generator = new Generator($entityManager, $webDirectory);	
		$uploader = new Uploader($webDirectory);		
		$grader = new Grader($entityManager);
						
		# get the current user
		$user= $this->get("security.token_storage")->getToken()->getUser();		
		if(!$user){
			return $this->returnForbiddenResponse("USER DOES NOT EXIST");
		}
		
		$postData = $request->request->all();		
		if(!isset($postData["assignmentId"]) || !($postData["assignmentId"] > 0)){
			return $this->returnForbiddenResponse("Assignment ID was not provided or not formatted properly");
		}
		
		$assignment = $entityManager->find("AppBundle\Entity\Assignment", $postData["assignmentId"]);		
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
		
		$entityManager->persist($problem);
		
		# instantiate the problem languages
		$qb = $entityManager->createQueryBuilder();
		$qb->select("l")
			->from("AppBundle\Entity\Language", "l")
			->where("1 = 1");
		$languages = $qb->getQuery()->getResult();
		
		foreach($languages as $language){

			$problemLanguage = new ProblemLanguage();

			$problemLanguage->language = $language;
			$problemLanguage->problem = $problem;
			$entityManager->persist($problemLanguage);
		}		
		
		# TESTCASES
		$postData = $request->request->all();
		$postTestcases = (array) json_decode($postData["testcases"]);		
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
					
				$entityManager->persist($testcase);
				$problem->testcases[] = $testcase;	
			} catch(Exception $e){
				return $this->returnForbiddenResponse($e->getMessage());
			}
		}		
		
		$entityManager->persist($problem);		
		$entityManager->flush();
							
		# INITIALIZE A SUBMISSION
		# create an entity for the current submission
		$submission = new Submission($problem, null, null);	

		# persist to the database to get the id
		$entityManager->persist($submission);
		$entityManager->flush();	
		
		# SETTING UP FOLDERS
		$subDirectory = $webDirectory."compilation/submissions/".$submission->id."/";
		
		$studentCodeDirectory = $subDirectory."student_code/";
		$compiledCodeDirectory = $subDirectory."compiled_code/";
		
		$flagsDirectory = $subDirectory."flags/";
		$customValidatorDirectory = $subDirectory."custom_validator/";
		
		$runLogDirectory = $subDirectory."run_logs/";
		$timeLogDirectory = $subDirectory."time_logs/";
		$diffLogDirectory = $subDirectory."diff_logs/";
		
		$inputFileDirectory = $subDirectory."input_files/";
		$outputFileDirectory = $subDirectory."output_files/";
		$argFileDirectory = $subDirectory."arg_files/";
		
		$userOutputDirectory = $subDirectory."user_output/";		
		
		# create all of the folders
		# make the directory for the submission output
		shell_exec("mkdir -p ".$subDirectory);
		shell_exec("mkdir -p ".$studentCodeDirectory);	
		shell_exec("mkdir -p ".$compiledCodeDirectory);	
		shell_exec("mkdir -p ".$flagsDirectory);	
		shell_exec("mkdir -p ".$customValidatorDirectory);	
		shell_exec("mkdir -p ".$runLogDirectory);	
		shell_exec("mkdir -p ".$timeLogDirectory);	
		shell_exec("mkdir -p ".$diffLogDirectory);	
		shell_exec("mkdir -p ".$inputFileDirectory);	
		shell_exec("mkdir -p ".$outputFileDirectory);	
		shell_exec("mkdir -p ".$argFileDirectory);	
		shell_exec("mkdir -p ".$userOutputDirectory);
		
		# GENERATE THE FILES
		$aceData = json_decode($postData["ACE"]);
		
		// make a temporary directory
		$tempdir = null;
		
		while(!is_dir($tempdir)){
			
			$tempdir = tempnam(sys_get_temp_dir(),"");
			
			if (file_exists($tempdir)){
				unlink($tempdir);
			}
			mkdir($tempdir);
		}
		$tempdir .= "/";
				
		$total_size = 0;
		
		$array_of_names = [];		
		foreach($aceData as $aceDatum){
			
			if(strlen($aceDatum->content) <= 0){
				return $this->returnForbiddenResponse("Your file cannot be empty");
			}
			
			if(strlen($aceDatum->filename) <= 0){
				return $this->returnForbiddenResponse("Your filename cannot be blank");
			}
			
			if(preg_match("/^[a-zA-Z0-9-_]+\.[a-zA-Z]+$/", $aceDatum->filename) <= 0){
				return $this->returnForbiddenResponse("Your filename is invalid");
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
		$file = fopen($target_file, "r");		
		if(!$file){
			return $this->returnForbiddenResponse("Could not properly open file for moving.");
		}
		
		# uploads directory 
		# get the submitted file path
		$uploadsDirectory = $webDirectory."compilation/uploads/".$user->id."/".$problem->id."/";
		$submittedFilePath = $uploadsDirectory."zippy.zip";

		# PUT THE ZIP FILE IN THE UPLOADS DIRECTORY
		$submittedFilename = $uploader->createGeneratorFile($user, $problem, "zippy.zip", $file);
		//return $this->returnForbiddenResponse(json_encode($submittedFilename));
		if(!$submittedFilename){
			$this->cleanUp($submission, $problem, $subDirectory, $uploadsDirectory);	
			return $this->returnForbiddenResponse("Unable to create file for submission");
		}

		// move into docker area
		shell_exec("mv ".$submittedFilePath." ".$studentCodeDirectory."/");

			# save the input/output files to a temp folder by deblobinating them	
		$testCaseGeneratorResult = $generator->generateTestcaseFiles($problem, $inputFileDirectory, $argFileDirectory, $outputFileDirectory);
		if($testCaseGeneratorResult != 1){			
			$this->cleanUp($submission, $problem, $subDirectory, $uploadsDirectory);	
			return $this->returnForbiddenResponse($testCaseGeneratorResult."");
		}
			
		# get the current language
		$language_id = $postData["language"];
		if(!isset($language_id) || !($language_id > 0)){
			$this->cleanUp($submission, $problem, $subDirectory, $uploadsDirectory);	
			return $this->returnForbiddenResponse("PROBLEM ID WAS NOT PROVIDED PROPERLY");
		}
		
		$language = $entityManager->find("AppBundle\Entity\Language", $language_id);
		if(!$language){
			$this->cleanUp($submission, $problem, $subDirectory, $uploadsDirectory);	
			return $this->returnForbiddenResponse("Language with id ".$language_id." does not exist");
		}		
		
		# set the main class and package name
		$mainClass = $postData["main_class"];
		$packageName = $postData["package_name"];
				
		# check main class and package name for validity
		if(!isset($mainClass) || strlen($mainClass) > 0 && preg_match("/^[a-zA-Z0-9_]+$/", $mainClass) != 1){
			return $this->returnForbiddenResponse("MAIN CLASS MUST BE ONLY LETTERS, NUMBERS, OR UNDERSCORES");
		}
		
		if(!isset($packageName) || strlen($packageName) > 0 && preg_match("/^[a-zA-Z0-9_]+$/", $packageName) != 1){
			return $this->returnForbiddenResponse("PACKAGE NAME MUST BE ONLY LETTERS, NUMBERS, OR UNDERSCORES");
		}

		$submission->main_class_name = $mainClass;
		$submission->package_name = $packageName;
		$submission->language = $language;
		$submission->submitted_file = "unimportant";
		$submission->filename = $submittedFilename;

		$isZipped = true;
		
		/* CREATE THE DOCKER CONTAINER */
		// required fields
		$dockerOptions = "";
		$dockerOptionsGeneratorResult = $generator->generateDockerOptions($dockerOptions, 
															$language, 
															$submittedFilename, 
															$problem, 
															$mainClass, 
															$packageName, 
															$isZipped, 
															false);
		
		if($dockerOptionsGeneratorResult != 1){			
			$this->cleanUp($submission, $problem, $subDirectory, $uploadsDirectory);	
			return $this->returnForbiddenResponse($dockerOptionsGeneratorResult."");
		}
		
		# RUN THE DOCKER COMPILATION
		$dockerTimeLimit = intval(count($problem->testcases) * ceil(floatval($problem->time_limit)/1000.0)) + 40;

		$dockerScript = $webDirectory."compilation/dockercompiler.sh \"".$dockerOptions."\" ".$submission->id." ".$dockerTimeLimit;
		$dockerOutput = shell_exec($dockerScript);	
		
		$dockerLogFile = fopen($flagsDirectory."docker_log", "w");
		if(!$dockerLogFile){
			$this->cleanUp($submission, $problem, $subDirectory, $uploadsDirectory);
			return $this->returnForbiddenResponse("Cannot open docker_script.log - contact a system admin");
		}
		fwrite($dockerLogFile, $dockerOutput);
		fclose($dockerLogFile);
		
		#return $this->returnForbiddenResponse($dockerOutput);
		
		# PARSE FOR SUBMISSION		
		$testcases = [];
		$submissionGeneratorResult = $generator->generateOutput($testcases, $submission, count($problem->testcases));
			
		if($submissionGeneratorResult != 1) {
		//	$this->cleanUp($submission, $problem, $subDirectory, $uploadsDirectory);
			return $this->returnForbiddenResponse($submissionGeneratorResult."");
		}
						
		# REMOVE TEMPORARY FOLDERS AND DATABASES
		$this->cleanUp($submission, $problem, $subDirectory, $uploadsDirectory);
		
		# RETURN THE TESTCASES OF THE RESULT
		$response = new Response(json_encode([		
			"testcases" => $testcases,			
		]));
		
		$response->headers->set("Content-Type", "application/json");
		$response->setStatusCode(Response::HTTP_OK);
	
		return $response;
	}
	
	// function to remove the submission and problem on failure
	private function cleanUp($submission, $problem, $subDirectory, $uploadsDirectory){
		
		# entity manager
		$entityManager = $this->getDoctrine()->getManager();	
		
		if(isset($submission)){
			$entityManager->remove($submission);
		}
		
		if(isset($problem)){
			$entityManager->remove($problem);
		}
		
		if(isset($subDirectory)){
			//shell_exec("rm -rf ".$subDirectory);
		}
		
		if(isset($uploadsDirectory)){
			shell_exec("rm -rf ".$uploadsDirectory);			
		}
		
		$entityManager->flush();
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
