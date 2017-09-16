<?php

namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
*@ORM\Entity
*@ORM\Table(name="testcaseresult")
**/
class TestcaseResult
{

	/**
	*@ORM\Column(type="integer")
	*@ORM\Id
	*@ORM\GeneratedValue(strategy="AUTO")
	*/
	private $id;

	/**
     * @ORM\ManyToOne(targetEntity="Submission")
     * @ORM\JoinColumn(name="submission_id", referencedColumnName="id")
     */
	private $submission;
	
	/**
     * @ORM\ManyToOne(targetEntity="Testcase")
     * @ORM\JoinColumn(name="testcase_id", referencedColumnName="id")
     */
	private $testcase;

	/**
	*@ORM\Column(type="boolean")
	*/
	private $is_correct;
	
	/**
	*@ORM\Column(type="integer")
	*/
	private $execution_time;
	
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