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
	
	public function __construct2($prob, $tm){
		$this->problem = $prob;
		$this->team = $tm;		
		$this->timestamp = new \DateTime("now");
		$this->is_accepted = false;
		#this->submission
		#this->filetype
		$this->main_class_name = "";
		#this->compiler_output
		$this->compiler_error = false;
		#this->language		
		$this->percentage = 0.0;
	}
		
	
	public function __construct11($prob, $tm, $time, $acc, $subm, $ft, $mainclass, $compout, $didcomp, $lang, $perc){
		$this->problem = $prob;
		$this->team = $tm;
		$this->timestamp = $time;
		$this->is_accepted = $acc;
		$this->submission = $subm;
		$this->filetype = $ft;
		$this->main_class_name = $mainclass;
		$this->compiler_output = $compout;
		$this->compiler_error = $didcomp;
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
	* @ORM\OneToMany(targetEntity="TestcaseResult", mappedBy="submission", cascade={"persist", "remove"})
	*/
	public $testcaseresults;
	
	/**
     * @ORM\ManyToOne(targetEntity="Problem")
     * @ORM\JoinColumn(name="problem_id", referencedColumnName="id")
     */
	public $problem;
	
	/**
     * @ORM\ManyToOne(targetEntity="Team", inversedBy="submissions")
     * @ORM\JoinColumn(name="team_id", referencedColumnName="id")
     */
	public $team;
	
	/**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
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
	public $submission;
	
	/**
	* @ORM\ManyToOne(targetEntity="Filetype")
	* @ORM\JoinColumn(name="filetype_id", referencedColumnName="id", nullable=true)
	*/
	public $filetype;
	
	/**
	* @ORM\Column(type="string", length=255)
	*/
	public $main_class_name;
	
	/**
	* @ORM\Column(type="blob", nullable=true)
	*/
	public $compiler_output;
	
	/**
	* @ORM\Column(type="boolean")
	*/
	public $compiler_error;
	
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