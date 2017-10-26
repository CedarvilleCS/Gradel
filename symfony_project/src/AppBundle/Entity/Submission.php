<?php

namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
*@ORM\Entity
*@ORM\Table(name="submission")
**/
class Submission {

	public function __construct(){
		
		$a = func_get_args();
		$i = func_num_args();	
		
		if(method_exists($this, $f='__construct'.$i)) {
			call_user_func_array(array($this,$f),$a);
		} else if($i != 0) {
			throw new Exception('Contructor does not accept '.$i.' arguments');
		}
		
		$this->testcaseresults = new ArrayCollection();	
	}
	
	public function __construct3($prob, $tm, $user){
		$this->problem = $prob;
		$this->team = $tm;
		$this->user = $user;
		$this->timestamp = new \DateTime("now");
		$this->is_accepted = false;
		#this->submission
		#this->filename
		$this->main_class_name = "";
		$this->package_name = "";
		#this->compiler_output
		$this->compiler_error = false;
		$this->exceeded_time_limit = false;
		$this->runtime_error = false;
		$this->max_runtime = -1;
		#this->language		
		$this->percentage = 0.0;
		#$this->final_good_testcase;
		$this->questionable_behavior = false;
		$this->is_complete = false;
	}
		
	
	public function __construct19($prob, $tm, $user, $time, $acc, $subm, $filename, $mainclass, $package, $compout, $didcomp, $didtime, $didrun, $maxtime, $lang, $perc, $tst, $ques, $complete){
		$this->problem = $prob;
		$this->user = $user;
		$this->team = $tm;
		$this->timestamp = $time;
		$this->is_accepted = $acc;
		$this->submission = $subm;
		$this->filename = $filename;
		$this->main_class_name = $mainclass;
		$this->package_name = $package;
		$this->compiler_output = $compout;
		$this->compiler_error = $didcomp;
		$this->exceeded_time_limit = $didtime;
		$this->max_runtime = $maxtime;
		$this->runtime_error = $didrun;
		$this->language = $lang;
		$this->percentage = $perc;
		$this->final_good_testcase = $tst;
		$this->questionable_behavior = $ques;
		$this->is_complete = $complete;
	}

	/**
	*@ORM\Column(type="integer")
	*@ORM\Id
	*@ORM\GeneratedValue(strategy="AUTO")
	*/
	public $id;

	/**
	* @ORM\OneToMany(targetEntity="TestcaseResult", mappedBy="submission")
	*/
	public $testcaseresults;
	
	/**
     * @ORM\ManyToOne(targetEntity="Problem")
     * @ORM\JoinColumn(name="problem_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
	public $problem;
	
	/**
     * @ORM\ManyToOne(targetEntity="Team", inversedBy="submissions")
     * @ORM\JoinColumn(name="team_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
	public $team;
	
	/**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
	public $user;
	
	/**
	*@ORM\Column(type="datetime")
	*/
	public $timestamp;

	/**
	* @ORM\Column(type="boolean")
	*/
	public $is_accepted;
	
	/**
	* @ORM\Column(type="boolean")
	*/
	public $is_complete;
	
	/**
	* @ORM\Column(type="blob", nullable=true)
	*/
	public $submission;
	
	public function deblobinateSubmission(){			
		return stream_get_contents($this->submission);
	}
	
	/**
	* @ORM\Column(type="string", length=255)
	*/
	public $main_class_name;
	
	/**
	* @ORM\Column(type="string", length=255)
	*/
	public $package_name;
	
	/**
	* @ORM\Column(type="blob", nullable=true)
	*/
	public $compiler_output;
	
	public function deblobinateCompilerOutput(){			
		return stream_get_contents($this->compiler_output);
	}
	
	/**
	* @ORM\Column(type="boolean")
	*/
	public $compiler_error;
	
	/**
	* @ORM\Column(type="boolean")
	*/
	public $exceeded_time_limit;
	
	/**
	* @ORM\Column(type="integer")
	*/
	public $max_runtime;	
	
	/**
	* @ORM\Column(type="boolean")
	*/
	public $runtime_error;
	
	/**
	* @ORM\ManyToOne(targetEntity="TestcaseResult")
	* @ORM\JoinColumn(name="final_good_testcase", referencedColumnName="id", nullable=true)
	*/
	public $final_good_testcase;
	
	/**
	* @ORM\Column(type="boolean")
	*/
	public $questionable_behavior;
	
	/**
	* @ORM\ManyToOne(targetEntity="Language")
	* @ORM\JoinColumn(name="language_id", referencedColumnName="id", nullable=true)
	*/
	public $language;
	
	/**
	*@ORM\Column(type="decimal", precision=12, scale=8)
	*/
	public $percentage;
}
?>