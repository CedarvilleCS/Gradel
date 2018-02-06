<?php

namespace AppBundle\Entity;

use JsonSerializable;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * @ORM\Entity
 * @ORM\Table(name="assignment")
 */
class Assignment implements JsonSerializable{
	
	public function __construct(){
		
		$a = func_get_args();
		$i = func_num_args();
		
		if(method_exists($this, $f='__construct'.$i)) {
			call_user_func_array(array($this,$f),$a);
		} else if($i != 0) {
			throw new Exception('ERROR: '.get_class($this).' constructor does not accept '.$i.' arguments');
		} else {
			$this->freeze_override = false;
		}
		
		$this->problems = new ArrayCollection();
		$this->teams = new ArrayCollection();
		$this->queries = new ArrayCollection();
	}
	
	public function __construct9($sect, $nm, $desc, $start, $end, $cutoff, $wght, $pen, $extra){
		$this->section = $sect;
		$this->name = $nm;
		$this->description = $desc;
		$this->start_time = $start;
		$this->end_time = $end;
		$this->cutoff_time = $cutoff;
		$this->weight = $wght;
		$this->is_extra_credit = $extra;
		$this->penalty_per_day = $pen;
		
		$this->freeze_time = null;
		$this->freeze_override = false;
	}
	
	public function __construct10($sect, $nm, $desc, $start, $end, $cutoff, $pen1, $pen2, $pen3, $pen4){
		$this->section = $sect;
		$this->name = $nm;
		$this->description = $desc;
		$this->start_time = $start;
		$this->end_time = $end;
		$this->cutoff_time = $cutoff;
		
		$this->weight = 1;
		$this->is_extra_credit = false;
		$this->penalty_per_day = 0;
		
		$this->penalty_per_wrong_answer = $pen1;
		$this->penalty_per_compile_error = $pen2;
		$this->penalty_per_time_limit = $pen3;
		$this->penalty_per_runtime_error = $pen4;
		
		$this->freeze_time = null;
		$this->freeze_override = false;
	}
	
	# clone method override
	public function __clone(){
		
		if($this->id){
			$this->id = null;
			
			# clone the problems
			$problemsClone = new ArrayCollection();
			
			foreach($this->problems as $problem){
				$problemClone = clone $problem;
				$problemClone->assignment = $this;
				
				$problemsClone->add($problemClone);
			}
			$this->problems = $problemsClone;
		}
	}
	
	/** 
	* @ORM\Column(type="integer")
	* @ORM\Id
	* @ORM\GeneratedValue(strategy="AUTO")
	*/
	public $id;

	/**
	* @ORM\OneToMany(targetEntity="Problem", mappedBy="assignment", cascade={"persist"})
	* @ORM\OrderBy({"weight" = "ASC", "name" = "ASC"});
	*/
	public $problems;

	/**
	* @ORM\ManyToOne(targetEntity="Section", inversedBy="assignments")
	* @ORM\JoinColumn(name="section_id", referencedColumnName="id", onDelete="CASCADE")
	*/
	public $section;

	/**
	* @ORM\Column(type="string", length=100)
	*/
	public $name;

	/**
	* @ORM\Column(type="text")
	*/
	public $description;

	/**
	* @ORM\OneToMany(targetEntity="Query", mappedBy="assignment", cascade={"persist"})
	* @ORM\OrderBy({"timestamp" = "ASC"});
	*/
	public $queries;
	
	/**
	* @ORM\Column(type="datetime")
	*/
	public $start_time;

	/**
	* @ORM\Column(type="datetime")
	*/
	public $end_time;
	
	/**
	* @ORM\Column(type="datetime")
	*/
	public $cutoff_time;

	/**
	* @ORM\Column(type="decimal", precision=12, scale=8, nullable=true)
	*/
	public $penalty_per_day;
	
	/**
	* @ORM\Column(type="integer")
	*/
	public $weight;

	/**
	* @ORM\Column(type="boolean")
	*/
	public $is_extra_credit;
	
	/**
	* @ORM\OneToMany(targetEntity="Team", mappedBy="assignment", cascade={"persist"}, orphanRemoval=true)
	*/
	public $teams;
	
	
	// Contest-Specific Information
	/**
	* @ORM\Column(type="datetime", nullable=true)
	*/
	public $freeze_time;
	
	/**
	* @ORM\Column(type="boolean")
	*/
	public $freeze_override;
	
	/**
	* @ORM\Column(type="integer", nullable=true)
	*/
	public $penalty_per_wrong_answer;
	
	/**
	* @ORM\Column(type="integer", nullable=true)
	*/
	public $penalty_per_compile_error;
	
	/**
	* @ORM\Column(type="integer", nullable=true)
	*/
	public $penalty_per_time_limit;
	
	/**
	* @ORM\Column(type="integer", nullable=true)
	*/
	public $penalty_per_runtime_error;	
	
	public function jsonSerialize(){
		return [
			'name' => $this->name,			
			'weight' => $this->weight,
			'teams' => $this->teams->toArray(),
		];
	}
}

?>
