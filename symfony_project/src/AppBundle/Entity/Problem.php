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
	
	public function __construct9($assign, $nm, $desc, $wght, $limit, $credit, $tot, $bef, $pen){
		$this->assignment = $assign;
		$this->name = $nm;
		$this->description = $desc;
		$this->weight = $wght;
		$this->time_limit = $limit;
		$this->is_extra_credit = $credit;
		
		$this->total_attempts = $tot;
		$this->attempts_before_penalty = $bef;
		$this->penalty_per_attempt = $pen;
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
	* @ORM\Column(type="integer")
	*/
	public $weight;

	/**
	* @ORM\Column(type="integer")
	*/
	public $time_limit;

	/**
	* @ORM\Column(type="boolean")
	*/
	public $is_extra_credit;
	
	/**
	*@ORM\Column(type="integer")
	*/
	public $total_attempts;
	
	/**
	*@ORM\Column(type="integer")
	*/
	public $attempts_before_penalty;
	
	/**
	* @ORM\Column(type="decimal", precision=12, scale=8)
	*/
	public $penalty_per_attempt;
}

?>
