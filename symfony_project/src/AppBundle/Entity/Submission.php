<?php

namespace AppBundle\Entity;

use JsonSerializable;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Config\Definition\Exception\Exception;

use AppBundle\Entity\Trial;
use AppBundle\Utils\Zipper;

/**
*@ORM\Entity
*@ORM\Table(name="submission")
**/
class Submission implements JsonSerializable {

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
	
	public function __construct2($trial, $tm){
		
		$this->problem = $trial->problem;
		$this->version = $trial->problem->version;
		$this->team = $tm;
		$this->user = $trial->user;
		$this->timestamp = new \DateTime("now");
		$this->best_submission = false;
		$this->submitted_file = $trial->file;
		#this->log_directory
		$this->filename = $trial->filename;
		$this->language = $trial->language;
		$this->main_class_name = $trial->main_class;
		$this->package_name = $trial->package_name;
		#this->compiler_output
		$this->compiler_error = false;
		$this->exceeded_time_limit = false;
		$this->runtime_error = false;
		$this->max_runtime = -1;
		$this->percentage = 0.0;
		$this->questionable_behavior = false;
		
		$this->pending_status = 0;
		$this->edited_timestamp = null;
		$this->student_message = null;
		$this->judge_message = null;
		$this->correct_override = false;
		$this->wrong_override = false;
		
		$this->is_completed = false;
		
	}
	
	public function __construct3($prob, $tm, $user){
		$this->problem = $prob;
		$this->version = $prob->version;
		$this->team = $tm;
		$this->user = $user;
		$this->timestamp = new \DateTime("now");
		$this->best_submission = false;
		#this->submitted_file
		#this->log_directory
		#this->filename2
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
		
		$this->pending_status = 0;
		$this->edited_timestamp = null;
		$this->student_message = null;
		$this->judge_message = null;
		$this->correct_override = false;
		$this->wrong_override = false;
		$this->reviewer = null;
		
		$this->is_completed = false;
	}
		
	public function __construct26($prob, $tm, $user, $time, $acc, $subm, $log, $filename, $mainclass, $package, $compout, $didcomp, $didtime, $didrun, $maxtime, $lang, $perc, $ques, $vers, $pend, $edit, $std_msg, $jdg_msg, $correct_over, $wrong_over, $rev){
		$this->problem = $prob;
		$this->user = $user;
		$this->team = $tm;
		$this->timestamp = $time;
		$this->best_submission = $acc;
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
		$this->version = $vers;
		
		$this->pending_status = $pend;
		$this->edited_timestamp = $edit;
		$this->student_message = $std_msg;
		$this->judge_message = $jdg_msg;
		$this->correct_override = $correct_over;
		$this->wrong_override = $wrong_over;
		$this->reviewer = $rev;
		
		$this->is_completed = false;
	}
	
	public function getResultString(){

		if($this->pending_status < 2){
			return "Pending";
		}
		// if it passes all the testcases		
		else if($this->isCorrect(true)){
			
			if($this->wrong_override){
				return "Incorrect - Judge Overriden";
			}

			return "Correct";
		}
		// if it didn't pass all testcases
		else if($this->correct_override){
			return "Correct - Judge Overriden";
		}
		else if($this->compiler_error) {
			return "Incorrect - Compiler Error";
		} 
		else if($this->runtime_error) {
			return "Incorrect - Runtime Error";
		} 
		else if($this->exceeded_time_limit) {
			return "Incorrect - Exceeded Time Limit";
		}
		else if($this->judge_message == null){
			return "Incorrect - Wrong Answer";
		} 
		else {
			return "Incorrect";
		}
	}

	public function isError(){
		return $this->compiler_error || $this->runtime_error || $this->exceeded_time_limit;		
	}
	
	public function isCorrect($raw = false){		

		$tcs = 0;
		$passed_tcs = 0;
		
		$tcs = [];
		if ($this->problem->testcase_counts[$this->version]) {
			$tcs = $this->problem->testcase_counts[$this->version];
			
			foreach($this->testcaseresults as $tcr){
				if($tcr->is_correct){
					$passed_tcs++;
				}			
			}
		}
		
		if($raw != true){
			
			if($this->correct_override) return true;		
			if($this->wrong_override) return false;
		}
		
		if($this->isError()){
			return false;
		}
		
		return $passed_tcs == $tcs;
	}

	# clone method override
	public function __clone(){
		
		if($this->id){
			$this->id = null;
			
			# clone the testcases
			$testcaseresultsClone = new ArrayCollection();
			
			foreach($this->testcaseresults as $testcaseresult){
				$testcaseresultClone = clone $testcaseresult;

				$testcaseresultClone->submission = $this;				
				$testcaseresultsClone->add($testcaseresultClone);
			}
			$this->testcaseresults = $testcaseresultsClone;
		}
		
	}

	/**
	*@ORM\Column(type="integer")
	*@ORM\Id
	*@ORM\GeneratedValue(strategy="AUTO")
	*/
	public $id;

	/**
	* @ORM\OneToMany(targetEntity="TestcaseResult", mappedBy="submission", cascade={"persist"}, fetch="EXTRA_LAZY")
	* @ORM\OrderBy({"testcase" = "ASC"})
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
     * @ORM\ManyToOne(targetEntity="Problem", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="problem_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
	public $problem;
	
	/**
	* @ORM\Column(type="integer")
	*/
	public $version;
	
	/**
     * @ORM\ManyToOne(targetEntity="Team", inversedBy="submissions", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="team_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
	public $team;
	
	/**
     * @ORM\ManyToOne(targetEntity="User", fetch="EXTRA_LAZY")
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
	public $best_submission;
	
	/**
	* @ORM\Column(type="boolean")
	*/
	public $is_completed;

	/**
	* @ORM\Column(type="blob", nullable=true)
	*/
	public $submitted_file;
	
	public function deblobinateSubmittedFile(){	
		$val = stream_get_contents($this->submitted_file);
		rewind($this->submitted_file);
		return $val;
	}
	
	public function getSubmissionFileContents(){
				
		// get the contents of a submission file
		$temp = tmpfile();
		$temp_filename = stream_get_meta_data($temp)['uri'];
		
		if(file_put_contents($temp_filename, $this->submitted_file) === FALSE){
			return false;			
		}
		
		$zipper = new Zipper();		
		$contents = $zipper->getZipContents($temp_filename);
		
		if($contents === false){
					
			fseek($temp, 0);
			
			return [['name'=>$this->filename, 'contents'=>fread($temp, filesize($temp_filename))]];
		}
		
		fclose($temp);

		return $contents;
	}
	
	/**
	* @ORM\OneToOne(fetch="EXTRA_LAZY") 
	* @ORM\Column(type="blob", nullable=true)
	*/
	public $log_directory;
	
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
	* @ORM\Column(type="text", nullable=true)
	*/
	public $compiler_output;
	
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
	
	// CONTEST-SPECIFIC THINGS
	/**
	* [0 = pending, 1 = claimed, 2 = reviewed]
	* @ORM\Column(type="integer")
	*/
	public $pending_status;
	
	/**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="reviewer_id", referencedColumnName="id", nullable=true)
     */
	public $reviewer;

	/**
	* @ORM\Column(type="datetime", nullable=true)
	*/
	public $edited_timestamp;
	
	/**
	* @ORM\Column(type="string", length=255, nullable=true)
	*/
	public $student_message;
	
	/**
	* @ORM\Column(type="string", length=255, nullable=true)
	*/
	public $judge_message;
	
	/**
	* @ORM\Column(type="boolean")
	*/
	public $correct_override;
	
	/**
	* @ORM\Column(type="boolean")
	*/
	public $wrong_override;	
	
	
	public function jsonSerialize(){

		return [
			'id' => $this->id,
			
			'team' => ($this->team) ? $this->team : ["name" => "NO TEAM", "member_string" => $this->user->getFullName()],
			'user' => $this->user,
						
			'problem' => [ 
				'id'=>$this->problem->id,
				'name'=>$this->problem->name,
				'assignment'=>$this->problem->assignment,
				'testcases'=>$this->problem->testcases->toArray(),
			],

			'timestamp' => $this->timestamp,
			
			'is_correct' => $this->isCorrect(false),
			'result_string' => $this->getResultString(),
						
			'runtime_error' => $this->runtime_error,
			'exceeded_time_limit' => $this->exceeded_time_limit,
			'compiler_error' => $this->compiler_error,
			
			'language' => $this->language,

			'reviewer' => $this->reviewer,

			'testcaseresults' => $this->testcaseresults->toArray(),
		];
	}
	
}
?>
