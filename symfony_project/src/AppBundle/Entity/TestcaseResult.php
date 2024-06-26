<?php

namespace AppBundle\Entity;

use JsonSerializable;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
*@ORM\Entity
*@ORM\Table(name="testcaseresult")
**/
class TestcaseResult implements JsonSerializable {

	public function __construct(){

		$a = func_get_args();
		$i = func_num_args();

		if(method_exists($this, $f='__construct'.$i)) {
			call_user_func_array(array($this,$f),$a);
		} else if($i != 0) {
			throw new Exception('ERROR: '.get_class($this).' constructor does not accept '.$i.' arguments');
		}
	}

	public function __construct8($sub, $test, $correct, $runout, $runerror, $time, $toolong, $out){
		$this->submission = $sub;
		$this->testcase = $test;
		$this->is_correct = $correct;
		$this->runtime_output = $runout;
		$this->runtime_error = $runerror;
		$this->execution_time = $time;
		$this->exceeded_time_limit = $toolong;
		$this->std_output = $out;
	}

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
     * @ORM\ManyToOne(targetEntity="Submission", inversedBy="testcaseresults")
     * @ORM\JoinColumn(name="submission_id", referencedColumnName="id", onDelete="CASCADE")
     */
	public $submission;

	/**
     * @ORM\ManyToOne(targetEntity="Testcase")
     * @ORM\JoinColumn(name="testcase_id", referencedColumnName="id", onDelete="CASCADE")
     */
	public $testcase;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	public $std_output;

	/**
	* @ORM\Column(type="text", nullable=true)
	*/
	public $runtime_output;

	/**
	* @ORM\Column(type="boolean")
	*/
	public $runtime_error;

	/**
	*@ORM\Column(type="boolean")
	*/
	public $is_correct;

	/**
	*@ORM\Column(type="integer")
	*/
	public $execution_time;

	/**
	* @ORM\Column(type="boolean")
	*/
	public $exceeded_time_limit;
	
	public function jsonSerialize(){
		return [
			'is_correct' => $this->is_correct,
			'std_output' => $this->std_output,
			'testcase' => $this->testcase,
		];
	}
}
?>
