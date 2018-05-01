<?php

namespace AppBundle\Utils;

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

use \DateTime;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class Generator  {
	
	public $em;
	public $web_dir;
	
	public function __construct($em, $web_dir) {
		
		if(stripos(get_class($em), "EntityManager") === FALSE){
			throw new Exception('The Generator class must be given a EntityManager but was given '.get_class($em));
		}
		
		if(strlen($web_dir) < 1){
			throw new Exception('The Generator class must be given a web directory');
		}
		
		$this->em = $em;
		$this->web_dir = $web_dir;
	}
	
	/* function to create a filename, language, main_class, package name based on some post information */
	/* returns a string on failure and 1 on success */
	public function generateFilename(&$filename, &$language, &$main_class, &$package_name, $problem, $postData){
		
		
		# check the language
		if(isset($postData["language"])){
			$language_id = $postData["language"];
		} else {
			$language_id = $postData["languageId"];
		}
		
		if(!isset($language_id) || !($language_id > 0)){
			return "Language ID was not provided";
		}
		
		$language = $this->em->find("AppBundle\Entity\Language", $language_id);
		if(!$language){
			 return "Language with ID".$language_id." does not exist";
		}
		if($language->name == "Java"){

			if((!isset($postData["main_class"]) || trim($postData["main_class"]) == "") && (!isset($postData["mainclass"]) || trim($postData["mainclass"]) == "")){
				 return "Main Class is required";
			}
			
			$main_class = null;
			if(!$postData["main_class"]){
				$main_class = $postData["mainclass"];
				$package_name = $postData["packagename"];
			} else {
				$main_class = $postData["main_class"];
				$package_name = $postData["package_name"];
			}

			$main_class = $main_class;
			$package_name = $package_name;

			$filename = $main_class.".java";

		} else {
			$main_class = "";
			$package_name = "";

			$filename = "problem".$problem->id.$language->filetype;
		}
		
		return 1;		
	}
	
	/* function to get the set the values for a submission*/
	/* returns a HTTP_FORBIDDEN response on failure and 1 on success */
	public function generateSubmission(&$submission, $problem, &$solvedAllTestcases){
		
		# necessary directories
		$sub_dir = $this->web_dir."compilation/submissions/".$submission->id."/";
		
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
		
		# PARSE THROUGH THE LOGS
		# default submission values
		$submission_is_compileerror = false;
		$submission_is_timelimit = false;
		$submission_is_runtimeerror = false;
		$submission_is_malicious = false;
		
		$submission_max_runtime = -1;
		$submission_percentage = 0.0;
		
		$compile_log = null;
		
		# check for errors
		if(file_exists($flags_dir."internal_error")){
			
			# echo "internal error! abort!";
			return "There was an internal docker error, probably custom validation related";
						
		} else if(file_exists($flags_dir."malicious")){
			
			# echo "malicious warning</br>";
			$submission_is_malicious = true;
			
			
		} else if(file_exists($flags_dir."compile_error")){
			# echo "compile error</br>";
			
			$compile_log = fopen($flags_dir."compile_error", "r");
			if(!$compile_log){
				return "Cannot open compile_error file";
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
			
			$compile_log = fopen($flags_dir."compile_warning", "r");
			
			if(!$compile_log){
				$compile_log = null;
			}
					
			# used to keep track of the total number of testcases passed
			$correct_testcase_count = 0;
			
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
						return "Cannot open ".$runtime_log_path;
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
						return "Cannot open ".$output_log_path;
					}
					$exectime_log = fopen($exectime_log_path, "r");
					if(!$exectime_log){
						return "Cannot open ".$exectime_log_path;
					}
					$diff_log = fopen($diff_log_path, "r");
					if(!$diff_log){
						return "Cannot open ".$diff_log_path;
					}
					
					# echo $tc->seq_num.") normal testcase</br>";
					
					
					# check the time limit
					$milliseconds = trim(fgets($exectime_log));		

					if(!is_numeric($milliseconds)){
						return "Error parsing the time file";
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
							$correct_testcase_count++;	
														
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
				$testcaseresult->runtime_output = stream_get_contents($runtime_log);
				$testcaseresult->runtime_error = $testcase_is_runtimeerror;
				$testcaseresult->execution_time = $testcase_exectime;
				$testcaseresult->exceeded_time_limit = $testcase_is_timelimit;
				$testcaseresult->std_output = stream_get_contents($user_output_log);
				
				$submission->testcaseresults->add($testcaseresult);
				
			}
		}
		
		# update the submission entity to the values decided aboce
		$submission->compiler_error = $submission_is_compileerror;
		$submission->compiler_output = stream_get_contents($compile_log);
		$submission->runtime_error = $submission_is_runtimeerror;
		$submission->questionable_behavior = $submission_is_malicious;
		$submission->exceeded_time_limit = $submission_is_timelimit; 
		$submission->max_runtime = $submission_max_runtime;
		$submission->percentage = $submission_percentage;

		$solvedAllTestcases = ($correct_testcase_count == $problem->testcases->count());
		
		/* return 1 on success */
		return 1;		
	}
	
	/* function to get the output from a submission as an array */
	/* this is used for generating correct output from a teacher submission */
	/* returns a HTTP_FORBIDDEN response on failure and 1 on success */
	public function generateOutput(&$testcases, $submission, $numTestcases){
		
		# necessary directories
		$sub_dir = $this->web_dir."compilation/submissions/".$submission->id."/";
		$flags_dir = $sub_dir."flags/";		
		$user_output_dir = $sub_dir."user_output/";	
		
		# check for an internal compilation again
		if(file_exists($flags_dir."internal_error")){
			
			return "There was an internal docker error";
						
		}
		# check for a malicious submission
		else if(file_exists($flags_dir."malicious")){
			
			return "The code you entered was malicious";				
			
		}
		# check for a compilation error
		else if(file_exists($flags_dir."compile_error")){
			
			return "The code you entered could not be compiled";
			
		}
		# check for a runtime error on a testcase
		else if(file_exists($flags_dir."runtime_error")){
			
			$runtime_error_log = fopen($flags_dir."runtime_error", "r");
			$testcase = stream_get_contents($runtime_error_log);
			
			return "The code you entered encountered a runtime error against testcase ".$testcase;
			
		}
		# check for overall time limit error
		else if(file_exists($flags_dir."time_limit")){
			
			return "The code you entered took too long to run";
		} 
		# loop through each testcase which ran
		else {
			
			$testcases = [];			
				
			for ($i = 1; $i <= $numTestcases; $i++) {
				
				$output_log_path = $user_output_dir.$i.".out";
				$user_output_log = fopen($output_log_path, "r");
				
				
				$testcases[] = stream_get_contents($user_output_log);
			}
		}

		// return 1 on success
		return 1;
	}

	/* Function to create the string that contains the command-line arguments for the compiler script in the Docker container */
	/* returns a HTTP_FORBIDDEN response on failure and 1 on success */
	public function generateDockerOptions(&$docker_options, $language, $submitted_filename, $problem, $main_class, $package_name, $is_zipped, $is_graded){
		
		$docker_options = "-l ". str_replace(' ','',$language->name)." -f ".$submitted_filename." -n ".count($problem->testcases);

		// optional Java fields
		if($language->name == "Java"){
			$docker_options = $docker_options." -M ".$main_class;
			
			if(strlen($package_name) > 0){
				$docker_options = $docker_options." -P ".$package_name;
			}
		}
		
		// is it a zip file
		if($is_zipped){
			$docker_options = $docker_options." -z";
		}
		
		// compilation options
		$compilation_options = null;
				
		$pb_problang = $this->em->createQueryBuilder();
		$pb_problang->select('pl')
				->from('AppBundle\Entity\ProblemLanguage', 'pl')
				->where('pl.problem = ?1')
				->andWhere('pl.language = ?2')
				->setParameter(1, $problem)
				->setParameter(2, $language);
				
		$pl_query = $pb_problang->getQuery();
		$prob_lang = $pl_query->getResult();	
		
		$prob_lang = $prob_lang[0];
		
		if(!isset($prob_lang)){
			return "YOU CANNOT SUBMIT A SOLUTION FOR THE GIVEN LANGUAGE";
		} else {
			$compilation_options = trim($prob_lang->compilation_options);
		}
		
		// loop through the command line options and add them to -c options (since spaces are annoying in BASH)
		if(strlen($compilation_options) > 0){
			
			$options = explode(" ", $compilation_options);
			
			foreach($options as $opt){
				$docker_options = $docker_options." -c ".$opt;
			}
		}
		
		// quit on first fail
		if($problem->stop_on_first_fail){
			$docker_options = $docker_options." -q";
		} else {
			$docker_options = $docker_options." -nq";			
		}
		
		// run the custom validator
		if($is_graded){
			$docker_options = $docker_options." -g";			
		} else {
			$docker_options = $docker_options." -ng";
		}
		
		// return 1 on success
		return 1;
	}
	
	/* Function to write the testcase input/args/output to files to be used in the docker container */
	/* returns a HTTP_FORBIDDEN response on failure and 1 on success */
	public function generateTestcaseFiles($problem, $input_file_dir, $arg_file_dir, $output_file_dir){
				
		foreach($problem->testcases as $tc){
			
			#INPUT FILE
			if(isset($tc->input)){
				
				// write the input file to the temp directory
				$input = $tc->input;			
				//return $input;
				#echo $input;
				
				$input_file = fopen($input_file_dir.$tc->seq_num.".in", "w");			
				if(!$input_file){
					return "Unable to open input file for writing";
				}
				
				fwrite($input_file, $input);
				fclose($input_file);
			}
			
			#ARG FILE
			if(isset($tc->command_line_input)){
				// write the input file to the temp directory
				$args = $tc->command_line_input;			
				
				#echo $args;
				
				$arg_file = fopen($arg_file_dir.$tc->seq_num.".args", "w");			
				if(!$arg_file){
					return "Unable to open command-line arg file for writing";
				}
				
				fwrite($arg_file, $args);
				fclose($arg_file);
			}
			
			#OUTPUT FILE
			if(isset($tc->correct_output)){
				// write the output file to the temp directory
				$correct_output = $tc->correct_output;
				
				#echo $correct_output;
				
				$output_file = fopen($output_file_dir.$tc->seq_num.".out", "w");
				if(!$output_file){
					 return "Unable to open output file for writing";
				}
				fwrite($output_file, $correct_output);
				fclose($output_file);
			}
		}
		
		// return 1 on success
		return 1;
	}

}

?>