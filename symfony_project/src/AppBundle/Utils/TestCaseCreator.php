<?php

namespace AppBundle\Utils;

use AppBundle\Entity\Problem;
use AppBundle\Entity\Testcase;
use AppBundle\Entity\Feedback;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;

class TestCaseCreator  {
	
	/* will make a testcase from the $data and return it in the parameters */
	/* returns 1 on success */
	public static function makeTestCase(&$testcase, $em, $problem, $data, $sequenceNumber){
		
		# check the required fields
		if((!isset($data['input']) && !isset($data['args'])) || !isset($data['output']) || !isset($data['weight'])){		  
			
			return "Not every required testcase field was provided!";		  
			
		} else {
		  
			if(!is_numeric(trim($data['weight'])) || (int)trim($data['weight']) < 1){
				return "The provided testcase weight is not greater than 0";
			}
		  
			$input = $data['input'];			
			$args = $data['args'];			
			$output = $data['output'];
			
			// add a newline to the input
			if($input != null && $input != "" && substr($input,-1) != "\n"){
				$input = $input."\n";
			} else if($input == null || $input == "") {
				$input = null;
			}
			
			// add a newline to the output
			if(isset($output) && substr($output,-1) != "\n"){
				$output = $output."\n";
			}
			
			$weight = $data['weight'];		  
		}

		# create the feedback
		$short_feedback = $data['short_response'];
		$long_feedback = $data['long_response'];
		
		$extra_credit = ($data['extra_credit'] == "true");
		
		if(isset($short_feedback) || isset($long_feedback)){
			
			if($short_feedback == null){
				$short_feedback = "";
			} else if($long_feedback == null){
				$long_feedback = "";
			}
			
			$feedback = new Feedback();
			$feedback->short_response = trim($short_feedback);
			$feedback->long_response = trim($long_feedback);
		}

		# get the testcase sequence number
		if(!($sequenceNumber > 0)){
			return "Sequence number not provided...please contact an admin";
		}
		
		$seq_num = $sequenceNumber;
		
		$testcase = new Testcase();

		$testcase->problem = $problem;
		$testcase->feedback = $feedback;
		$testcase->seq_num = $seq_num;
		
		if($input != null && $input != ""){
			
			$testcase->input = $input;
		}
		
		if($args != null && $args != ""){
			$testcase->command_line_input = $args;
		}
		
		$testcase->correct_output = $output;
		$testcase->weight = $weight;
		$testcase->is_extra_credit = $extra_credit;
	
		/* return 1 on success */
		return 1;
	}
}

?>