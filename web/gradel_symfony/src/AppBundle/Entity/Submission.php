<?php

namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
*@ORM\Entity
*@ORM\Table(name="submission")
**/
class Submission
{

	/**
	*@ORM\Column(type="integer")
	*@ORM\Id
	*@ORM\GeneratedValue(strategy="AUTO")
	*/
	private $id;

	/**
	* @ORM\OneToMany(targetEntity="Testcase", mappedBy="submission_id")
	*/
	private $testcaseresults;
	
	public function __construct() {
		$this->testcaseresults = new ArrayCollection();
	}
	
	/**
     * Many Submissions have One Problem.
     * @ORM\ManyToOne(targetEntity="Problem")
     * @ORM\JoinColumn(name="problem_id", referencedColumnName="id")
     */
	private $problem;
	
	/**
     * Many Submissions have One Team.
     * @ORM\ManyToOne(targetEntity="Team")
     * @ORM\JoinColumn(name="team_id", referencedColumnName="id")
     */
	private $team;
	
	/**
	*@ORM\Column(type="datetime")
	*/
	private $time;

	/**
	*@ORM\Column(type="boolean")
	*/
	private $is_accepted;
	
	/**
	*@ORM\Column(type="string", length=255)
	*/
	private $submitted_filename;
	
	/**
	*@ORM\Column(type="integer")
	*/
	private $submitted_filetype;
	
	/**
	*@ORM\Column(type="integer")
	*/
	private $language_id;
	
	/**
	*@ORM\Column(type="decimal", precision=12, scale=8)
	*/
	private $percentage;
	
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