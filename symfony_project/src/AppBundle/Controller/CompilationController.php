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
use AppBundle\Entity\ProblemGradingMethod;
use AppBundle\Entity\AssignmentGradingMethod;
use AppBundle\Entity\Feedback;
use AppBundle\Entity\TestcaseResult;

use AppBundle\Utils\Grader;

use \DateTime;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Psr\Log\LoggerInterface;

class CompilationController extends Controller {	
	
	/* name=submit */
	public function submitAction($problem_id, $language_id, $submitted_filename, $main_class, $package_name) {
	
		# entity manager
		$em = $this->getDoctrine()->getManager();						
		$grader = new Grader($em);
						
		# get the current user
		$user_entity= $this->get('security.token_storage')->getToken()->getUser();
		
		if(!$user_entity){
			die("USER DOES NOT EXIST");
		} else{
			echo($user_entity->getFirstName()." ".$user_entity->getLastName()."<br/>");
		}
		
		# get the current problem
		$problem_entity = $em->find("AppBundle\Entity\Problem", $problem_id);
		if(!$problem_entity){
			die("PROBLEM DOES NOT EXIST");
		} else {
			echo($problem_entity->id."<br/>");
		}
		
		# make sure that the assignment is still open for submission
		if($problem_entity->assignment->cutoff_time < new \DateTime("now")){
			die("TOO LATE TO SUBMIT FOR THIS PROBLEM");
		}
		
		# get the current team
		$team_entity = $grader->getTeam($user_entity, $problem_entity->assignment);		
		if(!$team_entity){
			die("TEAM DOES NOT EXIST");
		} else{			
			echo($team_entity->name."<br/>");		
		}		
		
		# make sure that you haven't submitted too many times yet
		$curr_attempts = $grader->getNumTotalAttempts($user_entity, $problem_entity);		
		if($problem_entity->gradingmethod->total_attempts > 0 && $curr_attempts >= $problem_entity->gradingmethod->total_attempts){
			die("ALREADY REACHED MAX ATTEMPTS FOR PROBLEM AT ".$curr_attempts);
		}
		
		# create an entity for the current submission
		$submission_entity = new Submission($problem_entity, $team_entity, $user_entity);	

		# persist to the database to get the id
		$em->persist($submission_entity);
		$em->flush();						
				
		# gets the gradel/symfony_project directory
		$web_dir = $this->get('kernel')->getProjectDir()."/";
		
		# all of the folder variables
		$submission_directory = $web_dir."compilation/submissions/".$submission_entity->id."/";
		$runtime_logs_directory = $submission_directory."runtime_logs/";
		$exectime_logs_directory = $submission_directory."time_logs/";
		$output_logs_directory = $submission_directory."output/";
		$diff_logs_directory = $submission_directory."diff_logs/";
		$code_directory = $submission_directory."code/";
		
		$code_to_submit_directory = $web_dir."/compilation/code_to_submit/".$submission_entity->id."/";
		
		$temp_folder = $web_dir."/compilation/temp/".$submission_entity->id."/";
		$temp_input_folder = $temp_folder."input/";
		$temp_output_folder = $temp_folder."output/";
		
		$uploads_directory = $web_dir."/compilation/uploads/".$user_entity->id."/".$problem_entity->id."/";
		
		# uploads directory 
		# get the submitted file path
		$submitted_file_path = $uploads_directory.$submitted_filename;
				
		# create all of the folders
		# make the directory for the submission output
		shell_exec("mkdir -p ".$submission_directory);		
		shell_exec("mkdir -p ".$runtime_logs_directory);
		shell_exec("mkdir -p ".$exectime_logs_directory);
		shell_exec("mkdir -p ".$output_logs_directory);
		shell_exec("mkdir -p ".$diff_logs_directory);
		shell_exec("mkdir -p ".$diff_logs_directory);
		shell_exec("mkdir -p ".$code_directory);
		
		# make the directory for the submitted code
		shell_exec("mkdir -p ".$code_to_submit_directory);
		
		# make the directory for the temporary stuff		
		shell_exec("mkdir -p ".$temp_folder);
		shell_exec("mkdir -p ".$temp_input_folder);
		shell_exec("mkdir -p ".$temp_output_folder);		
		
		# SETTING UP		
		# save the input/output files to a temp folder by deblobinating them
		foreach($problem_entity->testcases as $tc){
			// write the input file to the temp directory
			$input = stream_get_contents($tc->input);			
			
			echo $input;
			
			$input_file = fopen($temp_input_folder.$tc->seq_num.".in", "w") or die("Unable to open file for writing!");
			fwrite($input_file, $input);
			fclose($input_file);
			
			// write the output file to the temp directory
			$correct_output = stream_get_contents($tc->correct_output);
			
			echo $correct_output;
			
			$output_file = fopen($temp_output_folder.$tc->seq_num.".out", "w") or die("Unable to open file for writing!");
			fwrite($output_file, $correct_output);
			fclose($output_file);
		}
				
		# SUBMISSION CREATION AND COMPILATION
		# open the submitted file and prep for compilation
		# open the submitted file and prep for compilation
		$submitted_file = fopen($submitted_file_path, "r") or die ("Unable to open submitted file: ".$submitted_file_path);
		$submission_entity->submitted_file = $submitted_file;
		$submission_entity->filename = $submitted_filename;
		
		# query for the current filetype		
		$extension = pathinfo($submitted_file_path, PATHINFO_EXTENSION);
		$is_zipped = "false";
		if($extension == "zip"){
			$is_zipped = "true";
		};
				
		# get the current language
		$language_entity = $em->find("AppBundle\Entity\Language", $language_id);
		
		# get the problem language entity from the problem and language
		# store the compilation options from the problem language
		$compilation_options = null;
		
		$pb_problang = $em->createQueryBuilder();
		$pb_problang->select('pl')
				->from('AppBundle\Entity\ProblemLanguage', 'pl')
				->where('pl.problem = ?1')
				->andWhere('pl.language = ?2')
				->setParameter(1, $problem_entity)
				->setParameter(2, $language_entity);
				
		$pl_query = $pb_problang->getQuery();
		$prob_lang_entity = $pl_query->getOneOrNullResult();	
		
		if(!$prob_lang_entity){
			die("CANNOT SUBMIT A SOLUTION FOR THIS LANGUAGE!");
		} else {
			$compilation_options = trim($prob_lang_entity->compilation_options);
		}
						
		# set the main class and package name
		$submission_entity->main_class_name = $main_class;
		$submission_entity->package_name = $package_name;
			
		# RUN THE DOCKER COMPILATION
		$docker_time_limit = intval(count($problem_entity->testcases) * ceil(floatval($problem_entity->time_limit)/1000.0)) + 8 + rand(1,4);

		$docker_script = $web_dir."/compilation/dockercompiler.sh ".$problem_entity->id." ".$team_entity->id." ".dirname($submitted_file_path)." ".basename($submitted_file_path)." ".$language_entity->name." ".$is_zipped." ".$docker_time_limit." \"".$compilation_options."\" ".$submission_entity->id." ".$submission_entity->main_class_name." ".$submission_entity->package_name;
		# die($docker_script);
		
		$docker_output = shell_exec($docker_script);	
		
		$docker_log_file = fopen($submission_directory."docker_script.log", "w") or die("Cannot open docker_script.log");
		fwrite($docker_log_file, $docker_output);
		fclose($docker_log_file);
		
		# die(nl2br($docker_output));
		
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
		if(file_exists($submission_directory."compileerror") && file_exists($submission_directory."compiler.log")){
			# echo "compile error</br>";
			
			$compile_log = fopen($submission_directory."compiler.log", "r") or die("Cannot open compiler.log file");
			$submission_is_compileerror = true;
		} 
		# check for overall time limit error
		else if(file_exists($submission_directory."dockertimeout")){
			# echo "wall clock timeout</br>";
			
			$submission_is_timelimit = true;
		} 
		# loop through each testcase which ran
		else {
			
			# used to keep track of the total number of testcases passed
			$correct_testcase_count = 0;
			
			# used to keep track of the last correct testcase before things went south
			$already_wrong_tc = false;
			$update_last_correct = false;
			
			foreach($problem_entity->testcases as $tc){
				
				# default testcase result fields
				$testcase_is_correct = false;
				$testcase_is_runtimeerror = false;
				$testcase_is_timelimit = false;
				$testcase_is_malicious = false;
				$testcase_exectime = -1;
				
				# file paths
				$runtime_log_path = $runtime_logs_directory.$tc->seq_num.".log";
				$exectime_log_path = $exectime_logs_directory.$tc->seq_num.".log";
				$diff_log_path = $diff_logs_directory.$tc->seq_num.".log";
				$output_log_path = $output_logs_directory.$tc->seq_num.".out";
				
				$runtime_log = null;
				$exectime_log = null;
				$output_log = null;
				$diff_log = null;
				
				# check for runtime error
				if(file_exists($runtime_log_path) && filesize($runtime_log_path) > 0){
					
					$runtime_log = fopen($runtime_log_path, "r") or die("Cannot open ".$runtime_log_path);
					
					# echo $tc->seq_num.") runtime error</br>";					
					
					$testcase_is_runtimeerror = true;
					
				} 
				# the solution was normal
				else if(file_exists($runtime_log_path) && file_exists($output_log_path) && file_exists($exectime_log_path) && file_exists($diff_log_path)){
					
					$output_log = fopen($output_log_path, "r") or die("Cannot open ".$output_log_path);
					$exectime_log = fopen($exectime_log_path, "r") or die("Cannot open ".$exectime_log_path);
					$diff_log = fopen($diff_log_path, "r") or die("Cannot open ".$diff_log_path);
					
					# echo $tc->seq_num.") normal testcase</br>";
					
					
					# check the time limit
					$time_string = fgets($exectime_log);			
					if(sscanf($time_string, "user %dm%d.%ds", $minutes, $seconds, $milliseconds) == 3){
						$testcase_exectime = $seconds*1000+$milliseconds;
					} else{
						die("error parsing time_limit string");
					}
					
					if($testcase_exectime < 0 || $testcase_exectime > $problem_entity->time_limit){
						$testcase_is_timelimit = true;
					} 
					# check the diff log if timelimit was not exceeded
					else {
						
						# update submission min runtime
						if($testcase_exectime > $submission_max_runtime){
							$submission_max_runtime = $testcase_exectime;
						}
						
						$diff_string = fgets($diff_log);		
						$diff_string = str_replace(array("\r", "\n"),'',$diff_string);
						
						if(strcmp("YES", $diff_string) == 0){							
							$testcase_is_correct = true;
							$correct_testcase_count++;	
							
							# update submission_percentage
							$submission_percentage += max($tc->weight, floatval(1.0 / count($problem_entity->testcases)));
						} else {
							
							$already_wrong_tc = true;
							
						}
					}
				}
				// the solution was probably malicious
				else {
					# echo $tc->seq_num.") questionable behavior</br>";
					
					$testcase_is_malicious = true;
				}				
				
				# update submission values
				$submission_is_runtimeerror = $submission_is_runtimeerror || $testcase_is_runtimeerror;
				$submission_is_malicious = $submission_is_malicious || $testcase_is_malicious;
				$submission_is_timelimit = $submission_is_timelimit || $testcase_is_timelimit;
				
				#TestcaseResult($sub, $test, $correct, $runout, $runerror, $time, $toolong, $out)
				$testcaseresult_entity = new TestcaseResult($submission_entity, $tc, $testcase_is_correct, $runtime_log, $testcase_is_runtimeerror, $testcase_exectime, $testcase_is_timelimit, $output_log);
				$em->persist($testcaseresult_entity);
				$em->flush();
				
			}
		}
		
		# update the submission entity to the values decided aboce
		$submission_is_accepted = (!$submission_is_compileerror && !$submission_is_runtimeerror && ($correct_testcase_count == count($problem_entity->testcases)));
		
		$submission_entity->compiler_error = $submission_is_compileerror;
		$submission_entity->compiler_output = $compile_log;
		$submission_entity->runtime_error = $submission_is_runtimeerror;
		$submission_entity->questionable_behavior = $submission_is_malicious;
		$submission_entity->exceeded_time_limit = $submission_is_timelimit; 
		$submission_entity->max_runtime = $submission_max_runtime;
		$submission_entity->percentage = $submission_percentage;		
		
		# see if this new submission should be the accepted one
		$qb_accepted = $em->createQueryBuilder();
		$qb_accepted->select('s')
				->from('AppBundle\Entity\Submission', 's')
				->where('s.problem = ?1')
				->andWhere('s.team = ?2')
				->andWhere('s.is_accepted = true')
				->setParameter(1, $problem_entity)
				->setParameter(2, $team_entity);
				
		$acc_query = $qb_accepted->getQuery();
		$prev_accepted_sol = $acc_query->getOneOrNullResult();
	
		// take the new solution if it is 100% no matter what
		if($prev_accepted_sol && $submission_entity->percentage == 1){
			$submission_entity->is_accepted = true;
			$prev_accepted_sol->is_accepted = false;
		}
		// choose higher percentage if they both have percentages
		else if($prev_accepted_sol && $submission_entity->percentage > $prev_accepted_sol->percentage){
			#echo "New submission percentage ".$submission_entity->percentage." is greater than ".$prev_accepted_sol->percentage;
			$submission_entity->is_accepted = true;
			$prev_accepted_sol->is_accepted = false;
		}
		// don't change if the percentage is less
		else if($prev_accepted_sol && $submission_entity->percentage <= $prev_accepted_sol->percentage){
			#echo "This submission is garbage!";
			$submission_entity->is_accepted = false;			
		}		
		// choose the new one if it wasn't a compile error
		else if($prev_accepted_sol && !$submission_entity->compiler_error){
			#echo "New submission was not a compiler error!";
			$submission_entity->is_accepted = true;
			$prev_accepted_sol->is_accepted = false;
		}
		// otherwise only change if the previous accepted solution wasn't set
		else if(!$prev_accepted_sol){
			#echo "This is the first submission";
			$submission_entity->is_accepted = true;
		} 
		else{
			#echo "This submission is garbage!";
			$submission_entity->is_accepted = false;
		}
		
		if($prev_accepted_sol && $submission_entity->is_accepted){
			$em->persist($prev_accepted_sol);
		}
		
		# zip the submission directory for saving to the database
		if(!chdir($submission_directory)){
			die("Cannot switch directories!");
		}
		
		shell_exec("zip -r ".$submission_directory."log.zip *");
		
		if(!chdir($web_dir)){
			die("Cannot switch directories!");
		}
		
		$zip_file = fopen($submission_directory."log.zip", "r") or die("Cannot open log zip file for reading!");
		$submission_entity->log_directory = $zip_file;
				
		# remove the temp folder
		shell_exec("rm -rf ".$temp_folder);
		shell_exec("rm -rf ".$code_to_submit_directory);
		shell_exec("rm -rf ".$submission_directory);
		shell_exec("rm -rf ".$uploads_directory);
		
		# update the submission entity
		$em->persist($submission_entity);
		$em->flush();			
		
        return $this->redirectToRoute('problem_result', array('submission_id' => $submission_entity->id));
		//return new Response();
	}
}

?>
