<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="problem")
 */
class Problem{
	
	public function __construct(){
		
		$a = func_get_args();
		$i = func_num_args();
		
		if(method_exists($this, $f='__construct'.$i)) {
			call_user_func_array(array($this,$f),$a);
		} else if($i != 0) {
			throw new Exception('Contructor does not accept '.$i.' arguments');
		}
		
		$this->testcases = new ArrayCollection();
	}
	
	public function __construct13($assign, $nm, $desc, $inst, $lang, $default, $comp, $meth, $wght, $grdmeth, $attempts, $limit, $credit){
		$this->assignment = $assign;
		$this->name = $name;
		$this->description = $desc;
		$this->instructions = $inst;
		$this->language = $lang;
		$this->default_code = $default;
		$this->compilation_mode = $comp;
		$this->linking_option = $meth;
		$this->weight = $wght;
		$this->gradingmethod = $grdmeth;
		$this->attempts_allowed = $attempts;
		$this->time_limit = $limit;
		$this->is_extra_credit = $credit;
	}
	
	/** 
	* @ORM\Column(type="integer")
	* @ORM\Id
	* @ORM\GeneratedValue(strategy="AUTO")
	*/
	public $id;

	/**
	* @ORM\OneToMany(targetEntity="Testcase", mappedBy="problem")
	*/
	public $testcases;

	/**
	* @ORM\ManyToOne(targetEntity="Assignment", inversedBy="problems")
	* @ORM\JoinColumn(name="assignment_id", referencedColumnName="id")
	*/
	public $assignment;

	/**
	* @ORM\Column(type="string", length=100)
	*/
	public $name;

	/**
	* @ORM\Column(type="string", length=100)
	*/
	public $description;

	/**
	* @ORM\Column(type="string", length=100)
	*/
	public $instructions;

	/**
	* @ORM\ManyToOne(targetEntity="Language")
	* @ORM\JoinColumn(name="language_id", referencedColumnName="id")
	*/
	public $language;

	/**
	* @ORM\Column(type="string", length=10000)
	*/
	public $default_code;
	
	/**
	* @ORM\Column(type="string", length=10000)
	*/
	public $linking_option;
	
	/**
	* @ORM\Column(type="string", length=10000)
	*/
	public $compilation_mode;

	/**
	* @ORM\Column(type="decimal", precision=12, scale=8)
	*/
	public $weight;

	/**
	* @ORM\ManyToOne(targetEntity="Gradingmethod")
	* @ORM\JoinColumn(name="gradingmethod_id", referencedColumnName="id")
	*/
	public $gradingmethod;

	/**
	* @ORM\Column(type="integer")
	*/
	public $attempts_allowed;

	/**
	* @ORM\Column(type="integer")
	*/
	public $time_limit;

	/**
	* @ORM\Column(type="boolean")
	*/
	public $is_extra_credit;


	
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
	
	public function setCompilationMode($comp){
		$this->compilation_mode = $comp;
	}
	
	public function setLinkingOption($meth){
		$this->linking_option = $meth;
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
	
	public function getCompilationMode(){
		return $this->compilation_mode;
	}

	public function getLinkingOption(){
		return $this->linking_option;
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
