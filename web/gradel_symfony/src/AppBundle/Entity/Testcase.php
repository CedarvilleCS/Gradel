<?php

namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
*@ORM\Entity
*@ORM\Table(name="testcase")
**/
class Testcase
{

	/**
	*@ORM\Column(type="integer")
	*@ORM\Id
	*@ORM\GeneratedValue(strategy="AUTO")
	*/
	private $id;

	/**
     * Multiple TC per Problem
     * @ORM\ManyToOne(targetEntity="Problem")
     * @ORM\JoinColumn(name="problem_id", referencedColumnName="id")
     */
	private $problem;
	
	/**
	*@ORM\Column(type="integer")
	*/
	private $seq_num;

	/**
	*@ORM\Column(type="string", length=1044)
	*/
	private $input;
	
	/**
	*@ORM\Column(type="string", length=1044)
	*/
	private $correct_output;

	/**
     * Multiple FB per TC
     * @ORM\ManyToOne(targetEntity="Feedback")
     * @ORM\JoinColumn(name="feedback_id", referencedColumnName="id")
     */
	private $feedback;
	
	/**
	*@ORM\Column(type="decimal", precision=9, scale=8)
	*/
	private $weight;

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
	
	#GETTERZ
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