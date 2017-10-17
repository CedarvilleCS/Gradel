<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="assignment")
 */
class Assignment{
	
	public function __construct(){
		
		$a = func_get_args();
		$i = func_num_args();
		
		if(method_exists($this, $f='__construct'.$i)) {
			call_user_func_array(array($this,$f),$a);
		} else if($i != 0) {
			throw new Exception('Contructor does not accept '.$i.' arguments');
		}
		
		$this->problems = new ArrayCollection();
	}
	
	public function __construct9($sect, $nm, $desc, $start, $end, $cutoff, $wght, $grade, $extra){
		$this->section = $sect;
		$this->name = $nm;
		$this->description = $desc;
		$this->start_time = $start;
		$this->end_time = $end;
		$this->cutoff_time = $cutoff;
		$this->weight = $wght;
		$this->is_extra_credit = $extra;
		$this->gradingmethod = $grade;
	}
	
	/** 
	* @ORM\Column(type="integer")
	* @ORM\Id
	* @ORM\GeneratedValue(strategy="AUTO")
	*/
	public $id;

	/**
	* @ORM\OneToMany(targetEntity="Problem", mappedBy="assignment", cascade={"persist", "remove"})
	*/
	public $problems;

	/**
	* @ORM\ManyToOne(targetEntity="Section", inversedBy="assignments")
	* @ORM\JoinColumn(name="section_id", referencedColumnName="id")
	*/
	public $section;

	/**
	* @ORM\Column(type="string", length=100)
	*/
	public $name;

	/**
	* @ORM\Column(type="blob")
	*/
	public $description;
	
	public function deblobinateDescription(){			
		return stream_get_contents($this->description);
	}

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
	* @ORM\Column(type="decimal", precision=12, scale=8)
	*/
	public $weight;
	
	/**
	* @ORM\ManyToOne(targetEntity="Gradingmethod")
	* @ORM\JoinColumn(name="gradingmethod_id", referencedColumnName="id")
	*/
	public $gradingmethod;

	/**
	* @ORM\Column(type="boolean")
	*/
	public $is_extra_credit;
}

?>
