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
use AppBundle\Entity\AssignmentGradingMethod;
use AppBundle\Entity\Feedback;
use AppBundle\Entity\TestcaseResult;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class TestCaseCreator  {
	
	
	public static function makeTestCase($em, $problem, $data, $sequenceNumber){
		
		# check the required fields
		if((!isset($data['input']) && !isset($data['args'])) || !isset($data['output']) || !isset($data['weight'])){		  
			
			return self::returnForbiddenResponse("Not every required testcase field was provided!");		  
			
		} else {
		  
			if(!is_numeric(trim($data['weight'])) || (int)trim($data['weight']) < 1){
				return self::returnForbiddenResponse("The provided testcase weight is not greater than 0");
			}
		  
			$input = $data['input'];
			$args = $data['args'];
			$output = $data['output'];
			
			// add a newline to the output
			if(substr($output,-1) != "\n"){
				$output = $output."\n";
			}
			
			// make sure one of these was provided
			if($input == "" && $args == ""){
				return self::returnForbiddenResponse("Neither input or output was provided for testcase ".$sequenceNumber);
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
		if($problem->id > 0){
			
			$builder = $em->createQueryBuilder();
			$builder->select('tc')
				  ->from('AppBundle\Entity\Testcase', 'tc')
				  ->where('tc.problem = ?1')
				  ->setParameter(1, $problem)
				  ->orderBy('tc.seq_num', 'DESC')
				  ->setMaxResults(1);
			$query = $builder->getQuery();
			$last_testcase = $query->getOneOrNullResult();
			
			if($last_testcase){
				$seq_num = $last_testcase->seq_num + 1;
			} else {
				$seq_num = 1;
			}
			
		} else {
			
			if(!($sequenceNumber > 0)){
				return self::returnForbiddenResponse("Sequence number not provided...please contact an admin");
			}
			
			$seq_num = $sequenceNumber;
		}
		
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
	
		return $testcase;
	}	
	
	private static function returnForbiddenResponse($message){		
		$response = new Response($message);
		$response->setStatusCode(Response::HTTP_FORBIDDEN);
		return $response;
	}
}

?>