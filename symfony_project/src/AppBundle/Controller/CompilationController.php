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
	public function submitAction($problem_id=1) {
		
		$logger = $this->get('logger');
		
		$web_dir = $this->get('kernel')->getProjectDir();
		
		# these need to be passed in from the problem controller	
		//$submission_file_path = $web_dir."/compilation/test_code/malicious_delete_specific.c";
		//$submission_file_path = $web_dir."/compilation/test_code/sum.c";
		//$submission_file_path = $web_dir."/compilation/test_code/malicious_delete_all.c";
		//$submission_file_path = $web_dir."/compilation/test_code/while_loop.c";
		$submission_file_path = $web_dir."/compilation/test_code/malicious_add.c";
		$filetype_id = 1;		
		$language_id = 3;
		
		// this should be queried from the security token manager
		$user_id = 1;	
		
		// this should be queried based on the user ID
		$team_id = 1;
		
		
		
		$em = $this->getDoctrine()->getManager();
		
		# query for the current problem
		$qb = $em->createQueryBuilder();
		$qb->select('p')
			->from('AppBundle\Entity\Problem', 'p')
			->where('p.id = ?1')
			->setParameter(1, $problem_id);
			
		$query = $qb->getQuery();
		$problem_entity = $query->getOneorNullResult();
		
		if(!$problem_entity){
			$logger->critical("problem with id=".$problem_id." does not exist.");
			die("PROBLEM DOES NOT EXIST");
		}
		
		# query for the current team
		# TODO: add checks to make sure this user is allowed to submit for this problem in this situation
		$qb_team = $em->createQueryBuilder();
		$qb_team->select('t')
				->from('AppBundle\Entity\Team', 't')
				->where('t.id = ?1')
				->setParameter(1, $team_id);
				
		$query_team = $qb_team->getQuery();
		$team_entity = $query_team->getOneorNullResult();	

		if(!$team_entity){
			$logger->critical("team with id=".$team_id." does not exist.");
			die("TEAM DOES NOT EXIST");
		}
		
		$qb_user = $em->createQueryBuilder();
		$qb_user->select('u')
				->from('AppBundle\Entity\User', 'u')
				->where('u.id = ?1')
				->setParameter(1, $user_id);
				
		$query_user = $qb_user->getQuery();
		$user_entity = $query_user->getOneorNullResult();	

		if(!$user_entity){
			$logger->critical("user with id=".$user_id." does not exist.");
			die("USER DOES NOT EXIST");
		}
		
		
		# create a submission to edit in this controller 
		// and persist it to get the id number for later
		$submission_entity = new Submission($problem_entity, $team_entity, $user_entity);	
		
		$em->persist($submission_entity);
		$em->flush();					
			
		
		# SETTING UP and TEMP DIRECTORY
		
		# actually start the compilation process - move the problem into another variable	
		$temp_folder = $web_dir."/compilation/temp/".$submission_entity->id."/";
		$temp_input_folder = $temp_folder."input/";
		$temp_output_folder = $temp_folder."output/";
		
		# make the directory for the temporary stuff		
		shell_exec("mkdir -p ".$temp_folder);
		shell_exec("mkdir -p ".$temp_input_folder);
		shell_exec("mkdir -p ".$temp_output_folder);
		
		# save the input/output files to a temp folder
		# deblobinate the input/output files
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
		#echo nl2br($docker_output);
			
		# CREATE THE SUBMISSION ENTITY
		
		# PARSE THROUGH THE LOGS
		$submission_directory = $web_dir."/compilation/submissions/".$team_entity->id."/".$problem_entity->id."/".$submission_entity->id."/";
		$runtime_logs_directory = $submission_directory."runtime_logs/";
		$exectime_logs_directory = $submission_directory."time_logs/";
		$output_logs_directory = $submission_directory."output/";
		$diff_logs_directory = $submission_directory."diff_logs/";
		
		echo $submission_entity->id."<br/>";
		
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
		if(file_exists($submission_directory."compilerror")){
			echo "compile error</br>";
			
			$compile_log = fopen($submission_directory."compiler.log", "r") or die("Cannot open compiler.log file");
			$submission_is_compileerror = true;
		} 
		// check for overall time limit error
		else if(file_exists($submission_directory."dockertimeout")){
			echo "wall clock timeout</br>";
			
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
					
					echo $tc->seq_num.") runtime error</br>";					
					
					$testcase_is_runtimeerror = true;
					
				} 
				// the solution was normal
				else if(file_exists($runtime_log_path) && file_exists($output_log_path) && file_exists($exectime_log_path) && file_exists($diff_log_path)){
					
					$output_log = fopen($output_log_path, "r") or die("Cannot open ".$output_log_path);
					$exectime_log = fopen($exectime_log_path, "r") or die("Cannot open ".$exectime_log_path);
					$diff_log = fopen($diff_log_path, "r") or die("Cannot open ".$diff_log_path);
					
					echo $tc->seq_num.") normal testcase</br>";
					
					
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
					echo $tc->seq_num.") questionable behavior</br>";
					
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
		
		$submission_is_accepted = (!$submission_is_compileerror && !$submission_is_runtimeerror && ($correct_testcase_count == count($problem_entity->testcases)));
		
		$submission_entity->is_accepted = $submission_is_accepted;
		$submission_entity->compiler_error = $submission_is_compileerror;
		$submission_entity->compiler_output = $compile_log;
		$submission_entity->runtime_error = $submission_is_runtimeerror;
		$submission_entity->questionable_behavior = $submission_is_malicious;
		$submission_entity->exceeded_time_limit = $submission_is_timelimit; 
		$submission_entity->max_runtime = $submission_max_runtime;
		$submission_entity->percentage = $submission_percentage;
		
		# update the submission entity
		$em->persist($submission_entity);
		$em->flush();			
		
		shell_exec("rm -rf ".$temp_folder);
		
        //return $this->redirectToRoute('submission_results', array('submission_id' => $submission_entity->id));
		return new Response();
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
}




		
		/*
		# check for compilation error
		if(file_exists($submission_directory."compiler_errors.log")){
			
			$compile_log = fopen($submission_directory."compiler_errors.log", "r") or die("Unable to open compiler_errors file for reading!");
			$is_compile_error = true;
			
		} else {
		
			$num_testcases = count($problem_entity->testcases);
			$percentage = 0.0;
		
			# get the diff file to see how they did on the test cases
			# if this file does not exist we assume a time limit exception
			if(file_exists($submission_directory."testcase_exectime.log") && file_exists($submission_directory."compiler_warnings.log")){
				
				$compile_log = fopen($submission_directory."compiler_warnings.log", "r") or die("Unable to open compiler_warnings file for reading!");
				$is_compile_error = false;
				
				$time_log = fopen($submission_directory."testcase_exectime.log", "r") or die("Unable to open testcase_exectime file for reading!");
			
				foreach($problem_entity->testcases as &$tc){
					$log_dir = $submission_directory."logs/".$tc->seq_num.".log";
					$testcase_diff_dir = $submission_directory."testcase_diff".$tc->seq_num.".log";
					
					$time = -1;
					$testcase_is_correct = false;
					$did_exceed_time_limit = false;
					
					# check for runtime error
					if(file_exists($log_dir) &&  0 < filesize($log_dir)){
						
						$run_error_log = fopen($log_dir, "r") or die("Unable to open log file for reading!");
						$out_log = null;

						$is_runerror = true;
						
					} 
					# see if the diff file worked			
					else if(file_exists($testcase_diff_dir)){
						
						$diff_log = fopen($testcase_diff_dir, "r") or die("Unable to open testcase_diff".$tc->seq_num." file for reading!");
							
						$result = fgets($diff_log);		
						$result = str_replace(array("\r", "\n"), '',$result);
						
						$is_runerror = false;				
						
						# get the runtime 
						$time_line = fgets($time_log);			
						$n = sscanf($time_line, "user %dm%d.%ds", $minutes, $seconds, $milliseconds);
						$time = $seconds*1000+$milliseconds;
											
						$run_error_log = null;
						
						
						$out_dir = $submission_directory."output/".$tc->seq_num.".out";
						
						if(file_exists($out_dir)){
							
							$out_log = fopen($out_dir, "r") or die("Unable to open out file for reading!");
						
							if(strcmp("YES", $result) == 0){
								
								# check the time limits
								if($problem_entity->time_limit >= $time && $time >= 0){
								
									$testcase_is_correct = true;					
									
									$num_right++;
									
									if($tc->weight == 0){
										$percentage += 1.0/$num_testcases;
									} else {
										$percentage += $tc->weight;
									}
								} else {
									
									$testcase_is_correct = false;							
									$did_exceed_time_limit = true;						
								}
							}
							#docker quit in the middle of the running of this problem							
							else if(strcmp("TIME LIMIT", $result) == 0){	
								
								# do not count this test case and exit
								break;
								
							}
						} 
						# the output file did not exist
						else {
							
							# do not count this test case
							continue;
							
						}
						
					}
					# the testcase diff file did not exist
					else {
						
						continue;
						
					}
					
					$testcase_result = new TestcaseResult($submission_entity, $tc, $testcase_is_correct, $run_error_log, $is_runerror, $time, $did_exceed_time_limit, $out_log);
					
					# set the submission to be runtime or time limit if any test case exceeded
					$submission_entity->exceeded_time_limit = ($submission_entity->exceeded_time_limit || $did_exceed_time_limit);
					$submission_entity->runtime_error = ($submission_entity->runtime_error || $is_runerror);
					
					
					$em->persist($testcase_result);
					$em->flush();					
				}
				
			} 
			# compiler_warnings or testcase_exectime is missing
			else {					
				$submission_entity->exceeded_time_limit = true;			
			}
			
			fclose($diff_log);
		}
		
		
		$is_accepted = (!$is_compile_error && $num_right == $num_testcases);
		
		# finish the submission fields
		$submission_entity->compiler_output = $compile_log;
		$submission_entity->compiler_error = $is_compile_error;
		$submission_entity->percentage = $percentage;		
		$submission_entity->is_accepted = $is_accepted;
		*/



?>
