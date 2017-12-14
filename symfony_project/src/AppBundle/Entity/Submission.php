<?php

namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Config\Definition\Exception\Exception;

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
			throw new Exception('ERROR: '.get_class($this).' constructor does not accept '.$i.' arguments');
		}
		
		$this->testcaseresults = new ArrayCollection();	
	}
	
	public function __construct3($prob, $tm, $user){
		$this->problem = $prob;
		$this->team = $tm;
		$this->user = $user;
		$this->timestamp = new \DateTime("now");
		$this->is_accepted = false;
		#this->submitted_file
		#this->log_directory
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
		$this->questionable_behavior = false;
	}
		
	
	public function __construct18($prob, $tm, $user, $time, $acc, $subm, $log, $filename, $mainclass, $package, $compout, $didcomp, $didtime, $didrun, $maxtime, $lang, $perc, $ques){
		$this->problem = $prob;
		$this->user = $user;
		$this->team = $tm;
		$this->timestamp = $time;
		$this->is_accepted = $acc;
		$this->submitted_file = $subm;
		$this->log_directory = $log;
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
		$this->questionable_behavior = $ques;
	}
	
	public function isCorrect(){
		
		$tcs = 0;
		$extra_tcs = 0;
		
		$passed_tcs = 0;
		$passed_extra_tcs = 0;
		
		foreach($this->problem->testcases as $tc){
			if($tc->is_extra_credit){
				$extra_tcs++;
			} else {
				$tcs++;
			}
		}
		
		foreach($this->testcaseresults as $tcr){
			
			if(!$tcr->is_correct){
				continue;
			}
			
			if($tcr->testcase->is_extra_credit){
				$passed_extra_tcs++;
			} else {
				$passed_tcs++;
			}
		}	

		if(!$this->problem->extra_testcases_display){
			$passed_extra_tcs = 0;
			$extra_tcs = 0;
		}
		
		return $passed_tcs == $tcs && $passed_extra_tcs == $extra_tcs;
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
	
	public function getNumTestCasesCorrect(){
		
		$count = 0;
		foreach($this->testcaseresults as $tc){
			if($tc->is_correct){
				$count++;
			}
		}
		
		return $count;
	}
	
	/**
     * @ORM\ManyToOne(targetEntity="Problem")
     * @ORM\JoinColumn(name="problem_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
	public $problem;
	
	/**
     * @ORM\ManyToOne(targetEntity="Team", inversedBy="submissions")
     * @ORM\JoinColumn(name="team_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
	public $team;
	
	/**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
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
	* @ORM\Column(type="blob", nullable=true)
	*/
	public $submitted_file;
	
	public function deblobinateSubmittedFile(){			
		$val = stream_get_contents($this->submitted_file);
		rewind($this->submitted_file);
		return $val;
	}
	
	/**
	* @ORM\Column(type="blob", nullable=true)
	*/
	public $log_directory;
	
	public function deblobinateLogDirectory(){			
		$val = stream_get_contents($this->log_directory);
		rewind($this->log_directory);
		return $val;
	}
	
	/**
	* @ORM\Column(type="string", nullable=true)
	*/
	public $filename;
	
	
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
		$val = stream_get_contents($this->compiler_output);
		rewind($this->compiler_output);
		return $val;
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