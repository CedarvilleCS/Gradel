<?php

namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
*@ORM\Entity
*@ORM\Table(name="testcaseresult")
**/
class TestcaseResult {
	
	public function __construct(){
		
		$a = func_get_args();
		$i = func_num_args();
		
		if(method_exists($this, $f='__construct'.$i)) {
			call_user_func_array(array($this,$f),$a);
		} else if($i != 0) {
			throw new Exception('Contructor does not accept '.$i.' arguments');
		}
	}
	
	public function __construct4($sub, $test, $correct, $time){
		$this->submission = $sub;
		$this->testcase = $test;
		$this->is_correct = $correct;
		$this->execution_time = $time;
	}
	

	/**
	*@ORM\Column(type="integer")
	*@ORM\Id
	*@ORM\GeneratedValue(strategy="AUTO")
	*/
	public $id;

	/**
     * @ORM\ManyToOne(targetEntity="Submission", inversedBy="testcaseresults")
     * @ORM\JoinColumn(name="submission_id", referencedColumnName="id")
     */
	public $submission;
	
	/**
     * @ORM\ManyToOne(targetEntity="Testcase")
     * @ORM\JoinColumn(name="testcase_id", referencedColumnName="id")
     */
	public $testcase;

	/**
	*@ORM\Column(type="boolean")
	*/
	public $is_correct;
	
	/**
	*@ORM\Column(type="integer")
	*/
	public $execution_time;
	
	
	#SETTERS
	public function setSubmission($sub) {
		$this->submission = $sub;
	}
	
	public function setTestcase($tc) {
		$this->testcase = $tc;
	}
	
	public function setIsCorrect($correct) {
		$this->is_correct = $correct;
	}
	
	public function setExecutionTime($time) {
		$this->execution_time = $time;
	}
	
	#GETTERS
	public function getSubmission(){
		return $this->submission;
	}
	
	public function getTestcase(){
		return $this->testcase;
	}
	
	public function getIsCorrect(){
		return $this->is_correct;
	}
	
	public function getExecutionTime(){
		return $this->execution_time;
	}
}
?>