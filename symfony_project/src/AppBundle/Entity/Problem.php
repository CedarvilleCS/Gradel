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
	
	public function __construct12($assign, $nm, $desc, $inst, $lang, $default, $comp, $wght, $grdmeth, $attempts, $limit, $credit){
		$this->assignment = $assign;
		$this->name = $nm;
		$this->description = $desc;
		$this->instructions = $inst;
		$this->language = $lang;
		$this->default_code = $default;
		$this->compilation_options = $comp;
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
	* @ORM\Column(type="string", length=255)
	*/
	public $name;

	/**
	* @ORM\Column(type="blob")
	*/
	public $description;

	/**
	* @ORM\Column(type="blob")
	*/
	public $instructions;

	/**
	* @ORM\ManyToOne(targetEntity="Language")
	* @ORM\JoinColumn(name="language_id", referencedColumnName="id")
	*/
	public $language;

	/**
	* @ORM\Column(type="blob")
	*/
	public $default_code;

	/**
	* @ORM\Column(type="text")
	*/
	public $compilation_options;

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
}

?>
