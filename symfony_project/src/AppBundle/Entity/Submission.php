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
	
	public function __construct8($prob, $tm, $time, $acc, $filename, $filetype, $lang, $perc){
		$this->problem = $prob;
		$this->team = $time;
		$this->timestamp = $time;
		$this->is_accepted = $acc;
		$this->submitted_filename = $filename;
		$this->submitted_filetype = $filetype;
		$this->language = $lang;
		$this->percentage = $perc;
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
     * Many Submissions have One Problem.
     * @ORM\ManyToOne(targetEntity="Problem")
     * @ORM\JoinColumn(name="problem_id", referencedColumnName="id")
     */
	public $problem;
	
	/**
     * Many Submissions have One Team.
     * @ORM\ManyToOne(targetEntity="Team", inversedBy="submissions")
     * @ORM\JoinColumn(name="team_id", referencedColumnName="id")
     */
	public $team;
	
	/**
	*@ORM\Column(type="datetime")
	*/
	public $timestamp;

	/**
	*@ORM\Column(type="boolean")
	*/
	public $is_accepted;
	
	/**
	*@ORM\Column(type="string", length=1023)
	*/
	public $submitted_filename;
	
	/**
	*@ORM\Column(type="integer")
	*/
	public $submitted_filetype;
	
	/**
	* @ORM\ManyToOne(targetEntity="Language")
	* @ORM\JoinColumn(name="language_id", referencedColumnName="id")
	*/
	public $language;
	
	/**
	*@ORM\Column(type="decimal", precision=12, scale=8)
	*/
	public $percentage;
	
	# SETTERS
	public function setProblem($prob) {
		$this->problem = $prob;
	}

	public function setTeam($team) {
		$this->team = $team;
	}
	public function setTime($time) {
		$this->time = $time;
	}
	
	public function updateTime($time) {
		$this->time = new \DateTime("now");
	}
	
	public function setIsAccepted($accept) {
		$this->is_accepted = $accept;
	}
	
	public function setSubmittedFilename($subnm) {
		$this->submitted_filename = $subnm;
	}
	
	public function setSubmittedFiletype($subft) {
		$this->submitted_filetype = $subft;
	}
	
	public function setLanguageId($langid) {
		$this->language_id = $langid;
	}
	
	public function setPercentage($perc) {
		$this->percentage = $perc;
	}
	
	#GETTERZ
	public function getProblem(){
		return $this->problem;
	}
	
	public function getTeam(){
		return $this->team;
	}
	
	public function getTime(){
		return $this->time;
	}
	
	public function getIsAccepted(){
		return $this->is_accepted;
	}
	
	public function getSubmittedFilename(){
		return $this->submitted_filename;
	}
	
	public function getSubmittedFiletype(){
		return $this->submitted_filetype;
	}
	
	public function getLanguageId(){
		return $this->language_id;
	}
	
	public function getPercentage(){
		return $this->percentage;
	}
	
}
?>