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
	
	public function __construct11($prob, $tm, $time, $acc, $subm, $filetype, $mainclass, $compout, $didcomp, $lang, $perc){
		$this->problem = $prob;
		$this->team = $tm;
		$this->timestamp = $time;
		$this->is_accepted = $acc;
		$this->submission = $subm;
		$this->submitted_filetype = $filetype;
		$this->main_class_name = $mainclass;
		$this->compiler_output = $compout;
		$this->did_compile = $didcomp;
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
	*@ORM\Column(type="datetime")
	*/
	public $timestamp;

	/**
	* @ORM\Column(type="boolean")
	*/
	public $is_accepted;
	
	/**
	* @ORM\Column(type="blob")
	*/
	public $submission;
	
	/**
	* @ORM\Column(type="integer")
	*/
	public $submitted_filetype;
	
	/**
	* @ORM\Column(type="string", length=255)
	*/
	public $main_class_name;
	
	/**
	* @ORM\Column(type="blob")
	*/
	public $compiler_output;
	
	/**
	* @ORM\Column(type="boolean")
	*/
	public $compiler_error;
	
	/**
	* @ORM\ManyToOne(targetEntity="Language")
	* @ORM\JoinColumn(name="language_id", referencedColumnName="id")
	*/
	public $language;
	
	/**
	*@ORM\Column(type="decimal", precision=12, scale=8)
	*/
	public $percentage;
}
?>