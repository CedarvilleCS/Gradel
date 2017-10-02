<?php

namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

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
			throw new Exception('Contructor does not accept '.$i.' arguments');
		}
	}
	
	public function __construct6($prob, $seq, $in, $out, $feed, $wght){
		$this->problem = $prob;
		$this->seq_num = $seq;
		$this->input = $in;
		$this->correct_output = $out;
		$this->feedback = $feed;
		$this->weight = $wght;
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
     * @ORM\JoinColumn(name="problem_id", referencedColumnName="id")
     */
	public $problem;
	
	/**
	*@ORM\Column(type="integer")
	*/
	public $seq_num;

	/**
	*@ORM\Column(type="blob")
	*/
	public $input;
	
	/**
	*@ORM\Column(type="blob")
	*/
	public $correct_output;

	/**
     * Multiple FB per TC
     * @ORM\ManyToOne(targetEntity="Feedback")
     * @ORM\JoinColumn(name="feedback_id", referencedColumnName="id")
     */
	public $feedback;
	
	/**
	*@ORM\Column(type="decimal", precision=9, scale=8)
	*/
	public $weight;
	

	# SETTERS
	public function setProblem($prob) {
		$this->problem = $prob;
	}
	
	public function setSeqNum($seqnum) {
		$this->seq_num = $seqnum;
	}
	
	public function setInput($input) {
		$this->input = $input;
	}
	
	public function setCorrectOutput($corrout) {
		$this->correct_output = $corrout;
	}
	
	public function setFeedback($fb) {
		$this->feedback = $fb;
	}
	
	public function setWeight($weight) {
		$this->weight = $weight;
	}
	
	#GETTERS
	public function getProblem(){
		return $this->problem;
	}
	
	public function getSeqNum(){
		return $this->seq_num;
	}
	
	public function getInput(){
		return $this->input;
	}
	
	public function getCorrectOutput(){
		return $this->correct_output;
	}
	
	public function getFeedback(){
		return $this->feedback;
	}
	
	public function getWeight(){
		return $this->weight;
	}

}
?>