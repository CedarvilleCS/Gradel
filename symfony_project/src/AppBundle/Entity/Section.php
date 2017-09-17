<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="section")
 */
class Section{

	public function __construct(){
		
		$a = func_get_args();
		$i = func_num_args();
		
		if(method_exists($this, $f='__construct'.$i)) {
			call_user_func_array(array($this,$f),$a);
		} else if($i != 0){
			throw new Exception('Contructor does not accept '.$i.' arguments');
		}
		
		$this->assignments = new ArrayCollection();
	}
	
	public function __construct7($crs, $nm, $sem, $yr, $start, $end, $own){
		$this->course = $crs;
		$this->name = $nm;
		$this->semester = $sem;
		$this->year = $yr;
		$this->start_time = $start;
		$this->end_time = $end;
		$this->owner = $own;
	}

	/** 
	* @ORM\Column(type="integer")
	* @ORM\Id
	* @ORM\GeneratedValue(strategy="AUTO")
	*/
	private $id;

	/**
	* @ORM\OneToMany(targetEntity="Assignment", mappedBy="section")
	*/
	private $assignments;

	/**
	* @ORM\ManyToOne(targetEntity="Course", inversedBy="sections")
	* @ORM\JoinColumn(name="course_id", referencedColumnName="id")
	*/
	private $course;

	/**
	* @ORM\Column(type="string", length=100)
	*/
	private $name;

	/**
	* @ORM\Column(type="string", length=100)
	*/
	private $semester;

	/**
	* @ORM\Column(type="integer")
	*/
	private $year;

	/**
	* @ORM\Column(type="datetime")
	*/
	private $start_time;

	/**
	* @ORM\Column(type="datetime")
	*/
	private $end_time;

	/**
	* A user has a reference to an access level
	* @ORM\ManyToOne(targetEntity="User")
	* @ORM\JoinColumn(name="owner_id", referencedColumnName="id")
	*/
	private $owner;


	# SETTERS
	public function setCourse($curse){
		$this->course = $curse;
	}

	public function setName($nm){
		$this->name = $nm;
	}

	public function setSemester($sem){
		$this->semester = $sem;
	}

	public function setYear($yr){
		$this->year = $yr;
	}

	public function setStartTime($time){
		$this->start_time;
	}
	
	public function setEndTime($time){
		$this->end_time;
	}

	public function setOwner($owner){
		$this->owner;
	}

	
	# GETTERS
	public function getCourse(){
		return $this->course;
	}

	public function getName(){
		return $this->name;
	}

	public function getSemester(){
		return $this->semester;
	}

	public function	getYear(){
		return $this->year;
	}

	public function getStartTime(){
		return $this->start_time;
	}

	public function getEndTime(){
		return $this->end_time;
	}

	public function getOwner(){
		return $this->owner;
	}

}

?>
