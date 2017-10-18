<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Role;
use AppBundle\Entity\User;
use AppBundle\Entity\Team;
use AppBundle\Entity\Course;
use AppBundle\Entity\Section;
use AppBundle\Entity\Assignment;
use AppBundle\Entity\Problem;
use AppBundle\Entity\UserSectionRole;
use AppBundle\Entity\Testcase;
use AppBundle\Entity\Submission;
use AppBundle\Entity\Language;
use AppBundle\Entity\Gradingmethod;
use AppBundle\Entity\Filetype;
use AppBundle\Entity\Feedback;
use AppBundle\Entity\TestcaseResult;

use Psr\Log\LoggerInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\Response;

class CompilationController extends Controller {
	
	
	/* name=submit */
	public function submitAction($submission_id, $filetype_id, $language_id, $submitted_filename) {
		
		# entity manager
		$em = $this->getDoctrine()->getManager();		
				
		$web_dir = $this->get('kernel')->getProjectDir();
		
		# get the submitted file path
		$submission_file_path = $web_dir."/compilation/test_code/".$submitted_filename;
		
		# query for the current submission
		$qb_sub = $em->createQueryBuilder();
		$qb_sub->select('s')
			->from('AppBundle\Entity\Submission', 's')
			->where('s.id = ?1')
			->setParameter(1, $submission_id);
			
		$query_sub = $qb_sub->getQuery();
		$submission_entity = $query_sub->getOneorNullResult();
		
		if(!$submission_entity){
			die("SUBMISSION DOES NOT EXIST");
		} else if($submission_entity->is_complete) {
			echo("SUBMISSION IS ALREADY FINISHED");
		} else {
			echo($submission_entity->id."<br/>");
		}
		
		# get the current problem
		$problem_entity = $submission_entity->problem;
		
		if(!$problem_entity){
			die("PROBLEM DOES NOT EXIST");
		} else{
			echo($problem_entity->id."<br/>");	
		}
		
		
		# get the current user
		$user_entity= $this->get('security.token_storage')->getToken()->getUser();
		
		if(!$user_entity){
			die("USER DOES NOT EXIST");
		} else{
			echo($user_entity->getFirstName()." ".$user_entity->getLastName()."<br/>");
		}
		
		# get the current team
		$qb_teams = $em->createQueryBuilder();
		$qb_teams->select('t')
				->from('AppBundle\Entity\Team', 't')
				->where('t.assignment = ?1')
				->setParameter(1, $problem_entity->assignment);
				
		$query_team = $qb_teams->getQuery();
		$team_entities = $query_team->getResult();	

		# loop over all the teams for this assignment and figure out which team the user is a part of
		$team_entity = null;		
		foreach($team_entities as $team){			
		
			foreach($team->users as $user){					
			
				if($user_entity->id == $user->id){
					$team_entity = $team;
				}
			}
		}
		
		if(!$team_entity){
			die("TEAM DOES NOT EXIST");
		} else{			
			echo($team_entity->name."<br/>");		
		}
		
		# create all of the folders
		$submission_directory = $web_dir."/compilation/submissions/".$submission_entity->id."/";
		$runtime_logs_directory = $submission_directory."runtime_logs/";
		$exectime_logs_directory = $submission_directory."time_logs/";
		$output_logs_directory = $submission_directory."output/";
		$diff_logs_directory = $submission_directory."diff_logs/";
		$code_directory = $submission_directory."code/";
		
		$code_to_submit_directory = $web_dir."/compilation/code_to_submit/".$submission_entity->id."/";
		
		$temp_folder = $web_dir."/compilation/temp/".$submission_entity->id."/";
		$temp_input_folder = $temp_folder."input/";
		$temp_output_folder = $temp_folder."output/";
		
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
		# actually start the compilation process - move the problem into another variable	
		
		# save the input/output files to a temp folder by deblobinating them
		foreach($problem_entity->testcases as $tc){
			// write the input file to the temp directory
			$input = stream_get_contents($tc->input);			
			$input_file = fopen($temp_input_folder.$tc->seq_num.".in", "w") or die("Unable to open file for writing!");
			fwrite($input_file, $input);
			fclose($input_file);
			
			// write the output file to the temp directory
			$correct_output = stream_get_contents($tc->correct_output);
			$output_file = fopen($temp_output_folder.$tc->seq_num.".out", "w") or die("Unable to open file for writing!");
			fwrite($output_file, $correct_output);
			fclose($output_file);
		}
		
		
		# SUBMISSION CREATION AND COMPILATION
		# open the submitted file and prep for compilation
		# open the submitted file and prep for compilation
		$submitted_file = fopen($submission_file_path, "r") or die ("Unable to open submitted file: ".$submission_entity_file_path);
		$submission_entity->submission = $submitted_file;
		
		# query for the current filetype
		$qb_filetype = $em->createQueryBuilder();
		$qb_filetype->select('f')
				->from('AppBundle\Entity\Filetype', 'f')
				->where('f.id = ?1')
				->setParameter(1, $filetype_id);
				
		$qb_filetype = $qb_filetype->getQuery();
		$filetype_entity = $qb_filetype->getSingleResult();	
		
		$is_zipped = "false";
		if($filetype_entity->extension == "zip"){
			$is_zipped = "true";
		};
		
		$submission_entity->filetype = $filetype_entity;
		
		# query for the current language
		$qb_language = $em->createQueryBuilder();
		$qb_language->select('l')
				->from('AppBundle\Entity\Language', 'l')
				->where('l.id = ?1')
				->setParameter(1, $language_id);
				
		$qb_language = $qb_language->getQuery();
		$language_entity = $qb_language->getSingleResult();	
		
		$submission_entity->language = $language_entity;
				
		
		# RUN THE DOCKER COMPILATION
		$docker_time_limit = intval(count($problem_entity->testcases) * ceil(floatval($problem_entity->time_limit)/1000.0)) + 8 + rand(1,4);
		$docker_script = $web_dir."/compilation/dockercompiler.sh ".$problem_entity->id." ".$team_entity->id." ".dirname($submission_file_path)." ".basename($submission_file_path)." ".$language_entity->name." ".$is_zipped." ".$docker_time_limit." '".$problem_entity->compilation_options."' ".$submission_entity->id;
		
		#die($docker_script);
		
		$docker_output = shell_exec($docker_script);	
		# echo nl2br($docker_output);
		
		# PARSE THROUGH THE LOGS
		# echo $submission_entity->id."<br/>";
		
		# default submission values
		$submission_is_accepted = false;
		$submission_is_compileerror = false;
		$submission_is_timelimit = false;
		$submission_is_runtimeerror = false;
		$submission_is_malicious = false;
		
		$submission_max_runtime = -1;
		$submission_percentage = 0.0;
		
		# TODO: use this thing
		$submission_final_correct_testcase = null;		
		$compile_log = null;
		
		// check for compilation error
		# TODO: handle malicious touching of compilerror without a log file
		if(file_exists($submission_directory."compileerror")){
			# echo "compile error</br>";
			
			$compile_log = fopen($submission_directory."compiler.log", "r") or die("Cannot open compiler.log file");
			$submission_is_compileerror = true;
		} 
		// check for overall time limit error
		else if(file_exists($submission_directory."dockertimeout")){
			# echo "wall clock timeout</br>";
			
			$submission_is_timelimit = true;
		} 
		// loop through each testcase which ran
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
				
				// check for runtime error
				if(file_exists($runtime_log_path) && filesize($runtime_log_path) > 0){
					
					$runtime_log = fopen($runtime_log_path, "r") or die("Cannot open ".$runtime_log_path);
					
					# echo $tc->seq_num.") runtime error</br>";					
					
					$testcase_is_runtimeerror = true;
					
				} 
				// the solution was normal
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

							if(!$already_wrong_tc){
								$update_last_correct = true;
							}
							
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
				
				if($update_last_correct){
					$submission_final_correct_testcase = $testcaseresult_entity;
				}
				
			}
		}
		
		# update the submission entity to the values decided aboce
		$submission_is_accepted = (!$submission_is_compileerror && !$submission_is_runtimeerror && ($correct_testcase_count == count($problem_entity->testcases)));
		
		$submission_entity->is_accepted = $submission_is_accepted;
		$submission_entity->compiler_error = $submission_is_compileerror;
		$submission_entity->compiler_output = $compile_log;
		$submission_entity->runtime_error = $submission_is_runtimeerror;
		$submission_entity->questionable_behavior = $submission_is_malicious;
		$submission_entity->exceeded_time_limit = $submission_is_timelimit; 
		$submission_entity->max_runtime = $submission_max_runtime;
		$submission_entity->percentage = $submission_percentage;
		$submission_entity->final_good_testcase = $submission_final_correct_testcase;
		$submission_entity->is_complete = true;
		
		# update the submission entity
		$em->persist($submission_entity);
		$em->flush();			
		
		# remove the temp folder
		shell_exec("rm -rf ".$temp_folder);
		shell_exec("rm -rf ".$code_to_submit_directory);
		shell_exec("rm -rf ".$submission_directory);
		
        return $this->redirectToRoute('submission_results', array('submission_id' => $submission_entity->id));
		//return new Response();
	}
		
	/* name=submission_results */
	public function submissionAction($submission_id) {
		
		$em = $this->getDoctrine()->getManager();
		
		$qb = $em->createQueryBuilder();
		$qb->select('s')
			->from('AppBundle\Entity\Submission', 's')
			->where('s.id = ?1')
			->setParameter(1, $submission_id);
		
		$qb_submission = $qb->getQuery();
		$submission = $qb_submission->getSingleResult();	
		
		$compiler_output = stream_get_contents($submission->compiler_output);
		$submission_file = stream_get_contents($submission->submission);
		
		foreach($submission->testcaseresults as $tc){
			
			$output["std_output"] = stream_get_contents($tc->std_output);
			$output["runtime_output"] = stream_get_contents($tc->runtime_output);
			
			$tc_output[] = $output;			
		}
					
        return $this->render('compilation/submission/index.html.twig', [
			'submission' => $submission,
			'testcases_output' => $tc_output,
			'compiler_output' => $compiler_output,
			'submission_file' => $submission_file,
        ]);	
	}
	
	
	public function submit_backupAction($problem_id, $filetype_id, $language_id, $submitted_filename) {
			
		# entity manager
		$em = $this->getDoctrine()->getManager();		
				
		$web_dir = $this->get('kernel')->getProjectDir();
		
		# get the submitted file path
		$submission_file_path = $web_dir."/compilation/test_code/".$submitted_filename;
		
		# query for the current problem
		$qb = $em->createQueryBuilder();
		$qb->select('p')
			->from('AppBundle\Entity\Problem', 'p')
			->where('p.id = ?1')
			->setParameter(1, $problem_id);
			
		$query = $qb->getQuery();
		$problem_entity = $query->getOneorNullResult();
		
		if(!$problem_entity){
			die("PROBLEM DOES NOT EXIST");
		} else{
			echo($problem_entity->id."<br/>");	
		}
		
		
		# get the current user
		$user_entity= $this->get('security.token_storage')->getToken()->getUser();
		
		if(!$user_entity){
			die("USER DOES NOT EXIST");
		} else{
			echo($user_entity->getFirstName()." ".$user_entity->getLastName()."<br/>");
		}
		
		# get the current team
		$qb_teams = $em->createQueryBuilder();
		$qb_teams->select('t')
				->from('AppBundle\Entity\Team', 't')
				->where('t.assignment = ?1')
				->setParameter(1, $problem_entity->assignment);
				
		$query_team = $qb_teams->getQuery();
		$team_entities = $query_team->getResult();	

		# loop over all the teams for this assignment and figure out which team the user is a part of
		$team_entity = null;		
		foreach($team_entities as $team){			
		
			foreach($team->users as $user){					
			
				if($user_entity->id == $user->id){
					$team_entity = $team;
				}
			}
		}
		
		if(!$team_entity){
			die("TEAM DOES NOT EXIST");
		} else{			
			echo($team_entity->name."<br/>");		
		}
		
		
		# create a submission to edit in this controller 
		# and persist it to get the id number for later
		$submission_entity = new Submission($problem_entity, $team_entity, $user_entity);	
		
		$em->persist($submission_entity);
		$em->flush();					
			
		# create all of the folders
		$submission_directory = $web_dir."/compilation/submissions/".$submission_entity->id."/";
		$runtime_logs_directory = $submission_directory."runtime_logs/";
		$exectime_logs_directory = $submission_directory."time_logs/";
		$output_logs_directory = $submission_directory."output/";
		$diff_logs_directory = $submission_directory."diff_logs/";
		$code_directory = $submission_directory."code/";
		
		$code_to_submit_directory = $web_dir."/compilation/code_to_submit/".$submission_entity->id."/";
		
		$temp_folder = $web_dir."/compilation/temp/".$submission_entity->id."/";
		$temp_input_folder = $temp_folder."input/";
		$temp_output_folder = $temp_folder."output/";
		
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
		# actually start the compilation process - move the problem into another variable	
		
		# save the input/output files to a temp folder by deblobinating them
		foreach($problem_entity->testcases as $tc){
			// write the input file to the temp directory
			$input = stream_get_contents($tc->input);			
			$input_file = fopen($temp_input_folder.$tc->seq_num.".in", "w") or die("Unable to open file for writing!");
			fwrite($input_file, $input);
			fclose($input_file);
			
			// write the output file to the temp directory
			$correct_output = stream_get_contents($tc->correct_output);
			$output_file = fopen($temp_output_folder.$tc->seq_num.".out", "w") or die("Unable to open file for writing!");
			fwrite($output_file, $correct_output);
			fclose($output_file);
		}
		
		
		# SUBMISSION CREATION AND COMPILATION
		# open the submitted file and prep for compilation
		# open the submitted file and prep for compilation
		$submitted_file = fopen($submission_file_path, "r") or die ("Unable to open submitted file: ".$submission_entity_file_path);
		$submission_entity->submission = $submitted_file;
		
		# query for the current filetype
		$qb_filetype = $em->createQueryBuilder();
		$qb_filetype->select('f')
				->from('AppBundle\Entity\Filetype', 'f')
				->where('f.id = ?1')
				->setParameter(1, $filetype_id);
				
		$qb_filetype = $qb_filetype->getQuery();
		$filetype_entity = $qb_filetype->getSingleResult();	
		
		$is_zipped = "false";
		if($filetype_entity->extension == "zip"){
			$is_zipped = "true";
		};
		
		$submission_entity->filetype = $filetype_entity;
		
		# query for the current language
		$qb_language = $em->createQueryBuilder();
		$qb_language->select('l')
				->from('AppBundle\Entity\Language', 'l')
				->where('l.id = ?1')
				->setParameter(1, $language_id);
				
		$qb_language = $qb_language->getQuery();
		$language_entity = $qb_language->getSingleResult();	
		
		$submission_entity->language = $language_entity;
				
		
		# RUN THE DOCKER COMPILATION
		$docker_time_limit = intval(count($problem_entity->testcases) * ceil(floatval($problem_entity->time_limit)/1000.0)) + 8 + rand(1,4);
		$docker_script = $web_dir."/compilation/dockercompiler.sh ".$problem_entity->id." ".$team_entity->id." ".dirname($submission_file_path)." ".basename($submission_file_path)." ".$language_entity->name." ".$is_zipped." ".$docker_time_limit." '".$problem_entity->compilation_options."' ".$submission_entity->id;
		
		#die($docker_script);
		
		$docker_output = shell_exec($docker_script);	
		# echo nl2br($docker_output);
		
		# PARSE THROUGH THE LOGS
		# echo $submission_entity->id."<br/>";
		
		# default submission values
		$submission_is_accepted = false;
		$submission_is_compileerror = false;
		$submission_is_timelimit = false;
		$submission_is_runtimeerror = false;
		$submission_is_malicious = false;
		
		$submission_max_runtime = -1;
		$submission_percentage = 0.0;
		
		# TODO: use this thing
		$submission_final_correct_testcase = null;		
		$compile_log = null;
		
		// check for compilation error
		# TODO: handle malicious touching of compilerror without a log file
		if(file_exists($submission_directory."compileerror")){
			# echo "compile error</br>";
			
			$compile_log = fopen($submission_directory."compiler.log", "r") or die("Cannot open compiler.log file");
			$submission_is_compileerror = true;
		} 
		// check for overall time limit error
		else if(file_exists($submission_directory."dockertimeout")){
			# echo "wall clock timeout</br>";
			
			$submission_is_timelimit = true;
		} 
		// loop through each testcase which ran
		else {
			
			$correct_testcase_count = 0;
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
				
				// check for runtime error
				if(file_exists($runtime_log_path) && filesize($runtime_log_path) > 0){
					
					$runtime_log = fopen($runtime_log_path, "r") or die("Cannot open ".$runtime_log_path);
					
					# echo $tc->seq_num.") runtime error</br>";					
					
					$testcase_is_runtimeerror = true;
					
				} 
				// the solution was normal
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
		
		$submission_entity->is_accepted = $submission_is_accepted;
		$submission_entity->compiler_error = $submission_is_compileerror;
		$submission_entity->compiler_output = $compile_log;
		$submission_entity->runtime_error = $submission_is_runtimeerror;
		$submission_entity->questionable_behavior = $submission_is_malicious;
		$submission_entity->exceeded_time_limit = $submission_is_timelimit; 
		$submission_entity->max_runtime = $submission_max_runtime;
		$submission_entity->percentage = $submission_percentage;
		$submission_entity->is_complete = true;
		
		# update the submission entity
		$em->persist($submission_entity);
		$em->flush();			
		
		# remove the temp folder
		shell_exec("rm -rf ".$temp_folder);
		shell_exec("rm -rf ".$code_to_submit_directory);
		shell_exec("rm -rf ".$submission_directory);
		
        return $this->redirectToRoute('submission_results', array('submission_id' => $submission_entity->id));
		//return new Response();
	}
}

?>
