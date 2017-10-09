<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Testcase;
use AppBundle\Entity\Problem;
use AppBundle\Entity\Role;
use AppBundle\Entity\Submission;
use AppBundle\Entity\Filetype;
use AppBundle\Entity\Language;
use AppBundle\Entity\UserSectionRole;
use AppBundle\Entity\TestcaseResult;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class CompilationController extends Controller {
	
	
	/* name=problem */
	public function problemAction($problem_id=1) {
		
		# query for the current problem
		$em = $this->getDoctrine()->getManager();
		$qb = $em->createQueryBuilder();
		$qb->select('p')
			->from('AppBundle\Entity\Problem', 'p')
			->where('p.id = ?1')
			->setParameter(1, $problem_id);
			
		$query = $qb->getQuery();
		$problem = $query->getSingleResult();
		
		$description = stream_get_contents($problem->description);
		$instructions = stream_get_contents($problem->instructions);		
		$default_code = stream_get_contents($problem->default_code);
		
		# save the input/output files to a temp folder
		# deblobinate the input/output files
		foreach($problem->testcases as &$tc){
			
			// write the input file to the temp directory
			$tc->input = stream_get_contents($tc->input);			
			// write the output file to the temp directory
			$tc->correct_output = stream_get_contents($tc->correct_output);
			
			// save the feedback blobs			
			if(is_resource($tc->feedback->short_response) && get_resource_type($tc->feedback->short_response) == "stream"){
				$tc->feedback->short_response = stream_get_contents($tc->feedback->short_response);
			}
			if(is_resource($tc->feedback->long_response) && get_resource_type($tc->feedback->long_response) == "stream"){
				$tc->feedback->long_response = stream_get_contents($tc->feedback->long_response);
			}
		}
		
				
        return $this->render('compilation/problem/index.html.twig', [
			'problem' => $problem,
			'default_code' => $default_code,
			'instructions' => $instructions,
			'description' => $description,
        ]);	
	}
	
	
	/* name=submit */
	public function submitAction($problem_id=1) {
		
		# these need to be passed in from the controller that they submit it on		
		$web_dir = $this->get('kernel')->getProjectDir();
		$submission_file_path = $web_dir."/compilation/test_code/sum.c";
		$filetype_id = 1;		
		$language_id = 3;
		$team_id = 1;
		$em = $this->getDoctrine()->getManager();
		
		# query for the current problem
		$qb = $em->createQueryBuilder();
		$qb->select('p')
			->from('AppBundle\Entity\Problem', 'p')
			->where('p.id = ?1')
			->setParameter(1, $problem_id);
			
		$query = $qb->getQuery();
		$problem_entity = $query->getSingleResult();

		# query for the current team
		# add checks to make sure this user is allowed to submit for this problem in this situation
		$qb_team = $em->createQueryBuilder();
		$qb_team->select('t')
				->from('AppBundle\Entity\Team', 't')
				->where('t.id = ?1')
				->setParameter(1, $team_id);
				
		$query_team = $qb_team->getQuery();
		$team_entity = $query_team->getSingleResult();			
		
		# create a submission to edit in this controller
		$submission_entity = new Submission($problem_entity, $team_entity);
		
		$em->persist($submission_entity);
		$em->flush();			
		
		# actually start the compilation process - move the problem into another variable	
		$temp_folder = $web_dir."/compilation/temp/".$submission_entity->id."/";
		$temp_input_folder = $temp_folder."input/";
		$temp_output_folder = $temp_folder."output/";
		
		
		# SETTING UP and TEMP DIRECTORY
		$problem = $problem_entity;		
		
		$description = stream_get_contents($problem->description);
		$instructions = stream_get_contents($problem->instructions);		
		$default_code = stream_get_contents($problem->default_code);
				
		# make the directory for the temporary stuff		
		shell_exec("mkdir -p ".$temp_folder);
		shell_exec("mkdir -p ".$temp_input_folder);
		shell_exec("mkdir -p ".$temp_output_folder);
		
		# save the input/output files to a temp folder
		# deblobinate the input/output files
		foreach($problem->testcases as &$tc){
			
			// write the input file to the temp directory
			$tc->input = stream_get_contents($tc->input);			
			$file = fopen($temp_input_folder.$tc->seq_num.".in", "w") or die("Unable to open file for writing!");
			fwrite($file, $tc->input);
			fclose($file);
			
			// write the output file to the temp directory
			$tc->correct_output = stream_get_contents($tc->correct_output);
			$file = fopen($temp_output_folder.$tc->seq_num.".out", "w") or die("Unable to open file for writing!");
			fwrite($file, $tc->correct_output);
			fclose($file);
			
			// save the feedback blobs			
			if(is_resource($tc->feedback->short_response) && get_resource_type($tc->feedback->short_response) == "stream"){
				$tc->feedback->short_response = stream_get_contents($tc->feedback->short_response);
			}
			if(is_resource($tc->feedback->long_response) && get_resource_type($tc->feedback->long_response) == "stream"){
				$tc->feedback->long_response = stream_get_contents($tc->feedback->long_response);
			}
		}
		
		
		# SUBMISSION CREATION AND COMPILATION
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
		$docker_script = $web_dir."/compilation/dockercompiler.sh ".$problem_entity->id." ".$team_entity->id." ".dirname($submission_file_path)." ".basename($submission_file_path)." ".$language_entity->name." ".$is_zipped." 5 '".$problem_entity->compilation_options."' ".$submission_entity->id;
		
		#echo($docker_script);
		
		$docker_output = shell_exec($docker_script);	
		#echo nl2br($docker_output);
			
		# PARSE THROUGH THE COMPILATION LOG
		$output_directory = $web_dir."/compilation/submissions/".$team_entity->id."/".$problem_entity->id."/".$submission_entity->id."/";
				
		# check for compilation error
		if(file_exists($output_directory."compiler_errors.log")){
			
			$compile_log = fopen($output_directory."compiler_errors.log", "r") or die("Unable to open file for reading!");
			$is_compile_error = true;
			
		} else {
		
			$compile_log = fopen($output_directory."compiler_warnings.log", "r") or die("Unable to open file for reading!");
			$is_compile_error = false;
		
			# get the diff file to see how they did on the test cases
			$diff_log = fopen($output_directory."testcase_diff.log", "r") or die("Unable to open file for reading!");
			$time_log = fopen($output_directory."testcase_exectime.log", "r") or die("Unable to open file for reading!");
			
			$num_testcases = count($problem->testcases);
			$percentage = 0.0;

			foreach($problem->testcases as &$tc){
				$file_dir = $output_directory."output/".$tc->seq_num;
				
				$result = fgets($diff_log);		
				$result = str_replace(array("\r", "\n"), '',$result);
				
				$is_correct = false;
				$is_runerror = false;
				
				# check for runtime error
				if(file_exists($file_dir.".log")){
					
					$run_error_log = fopen($file_dir.".log", "r") or die("Unable to open file for reading!");
					$out_log = null;

					$is_runerror = true;
					
				} else{
				
					$run_error_log = null;
					$out_log = fopen($file_dir.".out", "r") or die("Unable to open file for reading!");
					if(strcmp("YES", $result) == 0){
						$is_correct = true;						
						
						$num_right++;
						
						if($tc->weight == 0){
							$percentage += 1.0/$num_testcases;
						} else {
							$percentage += $tc->weight;
						}
					}
				}
				
				$time_line = fgets($time_log);
				
				
				$n = sscanf($time_line, "user %dm%d.%ds", $minutes, $seconds, $milliseconds);
				$time = $seconds*1000+$milliseconds;
				
				$testcase_result = new TestcaseResult($submission_entity, $tc, $is_correct, $run_error_log, $is_runerror, $time, $out_log);
				$em->persist($testcase_result);
				$em->flush();
			}
			fclose($diff_log);
		}
		
		$is_accepted = ($num_right == $num_testcases);
		
		# finish the submission fields
		$submission_entity->compiler_output = $compile_log;
		$submission_entity->compiler_error = $is_compile_error;
		$submission_entity->percentage = $percentage;		
		$submission_entity->is_accepted = $is_accepted;
				
		# update the submission entity
		$em->persist($submission_entity);
		$em->flush();			
		
		shell_exec("rm -rf ".$temp_folder);
		
        return $this->render('compilation/problem/index.html.twig', [
			'problem' => $problem,
			'default_code' => $default_code,
			'instructions' => $instructions,
			'description' => $description,
        ]);
	}
	
	
	
	private function deblobinate($object){
		
		
	}
}
