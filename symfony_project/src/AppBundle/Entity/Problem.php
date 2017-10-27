<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Config\Definition\Exception\Exception;

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
			throw new Exception('ERROR: '.get_class($this).' constructor does not accept '.$i.' arguments');
		}
		
		$this->testcases = new ArrayCollection();
		$this->problem_languages = new ArrayCollection();
	}
	
	public function __construct8($assign, $nm, $desc, $wght, $grdmeth, $attempts, $limit, $credit){
		$this->assignment = $assign;
		$this->name = $nm;
		$this->description = $desc;
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
	* @ORM\OneToMany(targetEntity="ProblemLanguage", mappedBy="problem")
	*/
	public $problem_languages;

	/**
	* @ORM\ManyToOne(targetEntity="Assignment", inversedBy="problems")
	* @ORM\JoinColumn(name="assignment_id", referencedColumnName="id", nullable = false, onDelete="CASCADE")
	*/
	public $assignment;

	/**
	* @ORM\Column(type="string", length=255)
	*/
	public $name;

	/**
	* @ORM\Column(type="blob", nullable=true)
	*/
	public $description;
	
	public function deblobinateDescription(){			
		return stream_get_contents($this->description);
	}

	/**
	* @ORM\Column(type="decimal", precision=12, scale=8)
	*/
	public $weight;

	/**
	* @ORM\ManyToOne(targetEntity="Gradingmethod")
	* @ORM\JoinColumn(name="gradingmethod_id", referencedColumnName="id", nullable=true)
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
