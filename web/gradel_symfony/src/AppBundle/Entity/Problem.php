<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="problem")
 */
class Problem{
	/** 
	* @ORM\Column(type="integer")
	* @ORM\Id
	* @ORM\GeneratedValue(strategy="AUTO")
	*/
	private $id;

	/**
	* @ORM\OneToMany(targetEntity="Testcase", mappedBy="problem_id")
	*/
	private $testcases;

	public function __construct() {
		$this->testcases = new ArrayCollection();
	}

	/**
	* @ORM\ManyToOne(targetEntity="Assignment")
	* @ORM\JoinColumn(name="assignment_id", referencedColumnName="id")
	*/
	private $assignment;

	/**
	* @ORM\Column(type="string", length=100)
	*/
	private $name;

	/**
	* @ORM\Column(type="string", length=100)
	*/
	private $description;

	/**
	* @ORM\Column(type="string", length=100)
	*/
	private $instructions;

	/**
	* @ORM\ManyToOne(targetEntity="Language")
	* @ORM\JoinColumn(name="language_id", referencedColumnName="id")
	*/
	private $language;

	/**
	* @ORM\Column(type="string", length=65535)
	*/
	private $default_code;

	/**
	* @ORM\Column(type="decimal", precision=12, scale=8)
	*/
	private $weight;

	/**
	* @ORM\ManyToOne(targetEntity="Gradingmethod")
	* @ORM\JoinColumn(name="gradingmethod_id", referencedColumnName="id")
	*/
	private $gradingmethod;

	/**
	* @ORM\Column(type="integer")
	*/
	private $attempts_allowed;

	/**
	* @ORM\Column(type="integer")
	*/
	private $time_limit;

	/**
	* @ORM\Column(type="boolean")
	*/
	private $is_extra_credit;


	
	# SETTERS
	public function setAssignment($assign){
		$this->assignment = $assign;
	}

	public function setName($nm){
		$this->name = $nm;
	}

	public function setDescription($desc){
		$this->description = $desc;
	}

	public function setInstructions($ins){
		$this->instructions = $ins;
	}

	public function setLanguage($lang){
		$this->language = $lang;
	}

	public function setDefaultCode($code){
		$this->default_code = $code;
	}

	public function setWeight($wght){
		$this->weight = $wght;
	}

	public function setGradingmethod($grade){
		$this->gradingmethod = $grade;
	}

	public function setAttemptAllowed($att){
		$this->attempts_allowed = $att;
	}

	public function setTimeLimit($time){
		$this->time_limit = $time;
	}

	public function setIsExtraCredit($extra){
		$this->is_extra_credit = $extra;
	}


	# GETTERS
	public function getAssignment(){
		return $this->assignment;
	}

	public function getName(){
		return $this->name;
	}

	public function getDescription(){
		return $this->description;
	}

	public function getInstructions(){
		return $this->instructions;
	}

	public function getLanguage(){
		return $this->language;
	}

	public function getDefaultCode(){
		return $this->default_code;
	}

	public function getWeight(){
		return $this->weight;
	}

	public function getGradingmethod(){
		return $this->gradingmethod;
	}

	public function getAttemptsAllowed(){
		return $this->attempts_allowed;
	}

	public function getTimeLimit(){
		return $this->time_limit;
	}

	public function getIsExtraCredit(){
		return $this->is_extra_credit;
	}

}

?>
