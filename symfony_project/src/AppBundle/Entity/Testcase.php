<?php

namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
*@ORM\Entity
*@ORM\Table(name="testcase")
**/
class Testcase {

	public function __construct(){

		$a = func_get_args();
		$i = func_num_args();

		if(method_exists($this, $f='__construct'.$i)) {
			call_user_func_array(array($this,$f),$a);
		} else if($i != 0) {
			throw new Exception('ERROR: '.get_class($this).' constructor does not accept '.$i.' arguments');
		}
	}

	public function __construct8($prob, $seq, $in, $cmd, $out, $feed, $wght, $extra){
		$this->problem = $prob;
		$this->seq_num = $seq;
		$this->input = $in;
		$this->command_line_input = $cmd;
		$this->correct_output = $out;
		$this->feedback = $feed;
		$this->weight = $wght;
		$this->is_extra_credit = $extra;
	}
	
	public function __construct3($problem, $data, $seq){
		
		# check the required fields
		if((!isset($data['input']) && !isset($data['args'])) || !isset($data['output']) || !isset($data['weight'])){		  
			
			throw new Exception("Not every required testcase field was provided!");		  
			
		} else {
		  
			if(!is_numeric(trim($data['weight'])) || (int)trim($data['weight']) < 1){
				throw new Exception("The provided testcase weight is not greater than 0");
			}
		  
			$input = $data['input'];			
			$args = $data['args'];			
			$output = $data['output'];
			
			// add a newline to the input
			if($input == null || $input == "") {
				$input = null;
			}

			$weight = $data['weight'];		  
		}

		# create the feedback
		$short_feedback = $data['short_response'];
		$long_feedback = $data['long_response'];
		
		$extra_credit = ($data['extra_credit'] == "true");
		
		if((isset($short_feedback) && $short_feedback != "") || (isset($long_feedback) && $long_feedback != "")){
			
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
		if(!($seq > 0)){
			throw new Exception("Sequence number not provided...please contact an admin");
		}
		
		$seq_num = $seq;
		
		$this->problem = $problem;
		$this->feedback = $feedback;
		$this->seq_num = $seq_num;
		
		if($input != null && $input != ""){			
			$this->input = $input;
		}
		
		if($args != null && $args != ""){
			$this->command_line_input = $args;
		}
		
		$this->correct_output = $output;
		$this->weight = $weight;
		$this->is_extra_credit = $extra_credit;		
	}
	
	# clone method override
	public function __clone(){
		
		if($this->id){
			$this->id = null;	
		}
	}

	/**
	*@ORM\Column(type="integer")
	*@ORM\Id
	*@ORM\GeneratedValue(strategy="AUTO")
	*/
	public $id;

	/**
     * Multiple TC per Problem
     * @ORM\ManyToOne(targetEntity="Problem", inversedBy="testcases")
     * @ORM\JoinColumn(name="problem_id", referencedColumnName="id", onDelete="CASCADE")
     */
	public $problem;

	/**
	*@ORM\Column(type="integer")
	*/
	public $seq_num;

	/**
	*@ORM\Column(type="text", nullable=true)
	*/
	public $input;
	
	/**
	*@ORM\Column(type="text", nullable=true)
	*/
	public $command_line_input;

	/**
	*@ORM\Column(type="text")
	*/
	public $correct_output;

	/**
     * Multiple FB per TC
     * @ORM\ManyToOne(targetEntity="Feedback", cascade={"persist"})
     * @ORM\JoinColumn(name="feedback_id", referencedColumnName="id")
     */
	public $feedback;

	/**
	*@ORM\Column(type="integer")
	*/
	public $weight;
	
	/**
	*@ORM\Column(type="boolean")
	*/
	public $is_extra_credit;
}
?>
