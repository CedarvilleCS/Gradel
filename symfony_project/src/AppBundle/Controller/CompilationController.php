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
		
		# make sure that the assignment is still open for submission
		if($problem->assignment->cutoff_time < new \DateTime("now")){
			return $this->returnForbiddenResponse("TOO LATE TO SUBMIT FOR THIS PROBLEM");
		}
		
		if($problem->assignment->start_time > new \DateTime("now")){
			return $this->returnForbiddenResponse("TOO EARLY TO SUBMIT FOR THIS PROBLEM");
		}
		
		# get the current team
		$team = $grader->getTeam($user, $problem->assignment);		
		if(!$team){
			return $this->returnForbiddenResponse("YOU ARE NOT ON A TEAM FOR THIS ASSIGNMENT");
		}
		
		# make sure that you haven't submitted too many times yet
		$curr_attempts = $grader->getNumTotalAttempts($user, $problem);		
		if($problem->total_attempts > 0 && $curr_attempts >= $problem->total_attempts){
			return $this->returnForbiddenResponse("ALREADY REACHED MAX ATTEMPTS FOR PROBLEM AT ".$curr_attempts);
		}
		
		
		# PREP WORK
		# create an entity for the current submission
		$submission = new Submission($problem, $team, $user);	

		# persist to the database to get the id
		$em->persist($submission);
		$em->flush();						
				
		# gets the gradel/symfony_project directory
		$web_dir = $this->get('kernel')->getProjectDir()."/";
		
		# NEW COMPILATION
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
		$uploads_dir = $web_dir."/compilation/uploads/".$user->id."/".$problem->id."/";
		$submitted_file_path = $uploads_dir.$submitted_filename;
		
		# SETTING UP	
		
		# save the input/output files to a temp folder by deblobinating them
		foreach($problem->testcases as $tc){
			
			#INPUT FILE
			if($tc->input){
				// write the input file to the temp directory
				$input = $tc->deblobinateInput();			
				
				#echo $input;
				
				$input_file = fopen($input_file_dir.$tc->seq_num.".in", "w");			
				if(!$input_file){
					return $this->returnForbiddenResponse("Unable to open input file for writing - contact a system admin");
				}
				
				fwrite($input_file, $input);
				fclose($input_file);
			}
			// write the output file to the temp directory
			$correct_output = $tc->deblobinateCorrectOutput();
			
			#echo $correct_output;
			
			$output_file = fopen($output_file_dir.$tc->seq_num.".out", "w");
			if(!$output_file){
				 return $this->returnForbiddenResponse("Unable to open output file for writing - contact a system admin");
			}
			fwrite($output_file, $correct_output);
			fclose($output_file);
		}
		
		# save the custom validator to the temp folder
		shell_exec("cp ".$web_dir."compilation/custom_validator/*.* ".$custom_validator_dir);
		
		# get the custom validator
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
		
		
		# SUBMISSION CREATION AND COMPILATION
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
		$compilation_options = null;
		
		$pb_problang = $em->createQueryBuilder();
		$pb_problang->select('pl')
				->from('AppBundle\Entity\ProblemLanguage', 'pl')
				->where('pl.problem = ?1')
				->andWhere('pl.language = ?2')
				->setParameter(1, $problem)
				->setParameter(2, $language);
				
		$pl_query = $pb_problang->getQuery();
		$prob_lang = $pl_query->getOneOrNullResult();	
		
		if(!$prob_lang){
			return $this->returnForbiddenResponse("CANNOT SUBMIT A SOLUTION FOR THIS LANGUAGE!");
		} else {
			$compilation_options = trim($prob_lang->compilation_options);
		}
						
		# set the main class and package name
		$submission->main_class_name = $main_class;
		$submission->package_name = $package_name;
		
		
		/* CREATE THE DOCKER CONTAINER */
		// required fields
		$docker_options = "-l ".$language->name." -f ".$submitted_filename." -n ".count($problem->testcases);

		// optional Java fields
		if($language->name == "Java"){
			$docker_options = $docker_options." -M ".$main_class;
			
			if(strlen($package_name) > 0){
				$docker_options = $docker_options." -P ".$package_name;
			}
		}
		
		// optional other fields
		if($is_zipped){
			$docker_options = $docker_options." -z";
		}
		
		if(strlen($compilation_options) > 0){
			$docker_options = $docker_options." -c ".$compilation_options; 
		};
		
		// TODO IMPLEMENT THESE
		// graded
		
		// quit on first fail
		if($problem->stop_on_first_fail){
			$docker_options = $docker_options." -q";
		} else {
			$docker_options = $docker_options." -nq";			
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
		
		# PARSE THROUGH THE LOGS
		# default submission values
		$submission_is_accepted = false;
		$submission_is_compileerror = false;
		$submission_is_timelimit = false;
		$submission_is_runtimeerror = false;
		$submission_is_malicious = false;
		
		$submission_max_runtime = -1;
		$submission_percentage = 0.0;
		
		$compile_log = null;
		
		# check for compilation error
		if(file_exists($flags_dir."malicious")){
			
			# echo "malicious warning</br>";
			$submission_is_malicious = true;
			
			
		} else if(file_exists($flags_dir."compile_error")){
			# echo "compile error</br>";
			
			$compile_log = fopen($flags_dir."compile_error", "r");
			if(!$compile_log){
				return $this->returnForbiddenResponse("Cannot open compile_error file - contact a system admin");
			}
			$submission_is_compileerror = true;
		} 
		# check for overall time limit error
		else if(file_exists($flags_dir."time_limit")){
			# echo "wall clock timeout</br>";
			
			$submission_is_timelimit = true;
		} 
		# loop through each testcase which ran
		else {
			
			# used to keep track of the total number of testcases passed
			$correct_testcase_count = 0;
			$correct_extra_testcase_count = 0;
			
			# get the total testcase weights
			$total_points = 0;
			foreach($problem->testcases as $tc){
				
				if($tc->is_extra_credit){
					continue;
				}
				
				$total_points += $tc->weight;
			}
			
			$total_points = max(1, $total_points);
						
			foreach($problem->testcases as $tc){
				
				# default testcase result fields
				$testcase_is_correct = false;
				$testcase_is_runtimeerror = false;
				$testcase_is_timelimit = false;
				$testcase_is_malicious = false;
				$testcase_exectime = -1;
				
				# file paths
				$runtime_log_path = $run_log_dir.$tc->seq_num.".log";
				$exectime_log_path = $time_log_dir.$tc->seq_num.".log";
				$diff_log_path = $diff_log_dir.$tc->seq_num.".log";
				$output_log_path = $user_output_dir.$tc->seq_num.".out";
				
				#echo $runtime_log_path;
				#echo $exectime_log_path;
				#echo $diff_log_path;
				#echo $output_log_path;
				
				#die();
				
				$runtime_log = null;
				$exectime_log = null;
				$user_output_log = null;
				$diff_log = null;
				
				# check for runtime error
				if(file_exists($runtime_log_path) && filesize($runtime_log_path) > 0){
					
					$runtime_log = fopen($runtime_log_path, "r");
					if(!$runtime_log){
						return $this->returnForbiddenResponse("Cannot open ".$runtime_log_path);
					}
					# echo $tc->seq_num.") runtime error</br>";					
					
					$testcase_is_runtimeerror = true;
					
				} 
				# the lack of a runtime means that the testcase wasn't run
				else if(!file_exists($runtime_log_path)){
					break;
				}
				# the solution was normal
				else {
					
					$user_output_log = fopen($output_log_path, "r");
					if(!$user_output_log){
						return $this->returnForbiddenResponse("Cannot open ".$output_log_path." - contact a system admin");
					}
					$exectime_log = fopen($exectime_log_path, "r");
					if(!$exectime_log){
						return $this->returnForbiddenResponse("Cannot open ".$exectime_log_path." - contact a system admin");
					}
					$diff_log = fopen($diff_log_path, "r");
					if(!$diff_log){
						return $this->returnForbiddenResponse("Cannot open ".$diff_log_path." - contact a system admin");
					}
					
					# echo $tc->seq_num.") normal testcase</br>";
					
					
					# check the time limit
					$milliseconds = trim(fgets($exectime_log));		

					if(!is_numeric($milliseconds)){
						return $this->returnForbiddenResponse("error parsing the time file - contact a system admin");
					}
					
					$testcase_exectime = $milliseconds;
					
					if($testcase_exectime < 0 || $testcase_exectime > $problem->time_limit){
						$testcase_is_timelimit = true;
					} 
					# check the diff log if timelimit was not exceeded
					else {
						
						# update submission min runtime
						if($testcase_exectime > $submission_max_runtime){
							$submission_max_runtime = $testcase_exectime;
						}
						
						$diff_string = trim(fgets($diff_log));		
						$diff_string = str_replace(array("\r", "\n"),'',$diff_string);
						
						if(strcmp("YES", $diff_string) == 0){							
							$testcase_is_correct = true;
							
							
							if($tc->is_extra_credit){
								$correct_extra_testcase_count++;
							} else{
								$correct_testcase_count++;	
							}
							
							# update submission_percentage
							$submission_percentage += $tc->weight/$total_points;
						}
					}
				}			
				
				# update submission values
				$submission_is_runtimeerror = $submission_is_runtimeerror || $testcase_is_runtimeerror;
				$submission_is_timelimit = $submission_is_timelimit || $testcase_is_timelimit;
		
				$testcaseresult = new TestcaseResult();
				
				$testcaseresult->submission = $submission;
				$testcaseresult->testcase = $tc;
				$testcaseresult->is_correct = $testcase_is_correct;
				$testcaseresult->runtime_output = $runtime_log;
				$testcaseresult->runtime_error = $testcase_is_runtimeerror;
				$testcaseresult->execution_time = $testcase_exectime;
				$testcaseresult->exceeded_time_limit = $testcase_is_timelimit;
				$testcaseresult->std_output = $user_output_log;
				
				$em->persist($testcaseresult);
				$em->flush();
				
			}
		}
		
		# update the submission entity to the values decided aboce
		$submission_is_accepted = (!$submission_is_compileerror && !$submission_is_runtimeerror && ($correct_testcase_count == count($problem->testcases)));
		
		$submission->compiler_error = $submission_is_compileerror;
		$submission->compiler_output = $compile_log;
		$submission->runtime_error = $submission_is_runtimeerror;
		$submission->questionable_behavior = $submission_is_malicious;
		$submission->exceeded_time_limit = $submission_is_timelimit; 
		$submission->max_runtime = $submission_max_runtime;
		$submission->percentage = $submission_percentage;		
		
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
		if($this->acceptSubmission($submission, $prev_accepted_sol, $correct_extra_testcase_count + $correct_testcase_count)){
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
		#shell_exec("rm -rf ".$sub_dir);
		#shell_exec("rm -rf ".$uploads_dir);
		
		# update the submission entity
		$em->persist($submission);
		$em->flush();
		
		# return the url
        $url = $this->generateUrl('problem_result', array('submission_id' => $submission->id));
		
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
	
	private function acceptSubmission($submission, $previous, $total_correct){
		
		// take the new solution if it is 100% no matter wha
		$total_testcases = count($submission->problem->testcases);
		
		if($total_correct == $total_testcases){
			#echo "This new testcase solves all of the testcases!";
			return true;
		}
		// choose higher percentage if they both have percentages
		else if($previous && $submission->percentage > $previous->percentage){
			#echo "This new one has a higher percentage!";
			return true;
		}
		else {
			#echo "Only change if the old one isn't set";
			return $previous == null;
		}
		
	}
}

?>
