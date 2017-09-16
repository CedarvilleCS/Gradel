<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="assignment")
 */
class Assignment{
	/** 
	* @ORM\Column(type="integer")
	* @ORM\Id
	* @ORM\GeneratedValue(strategy="AUTO")
	*/
	private $id;

	/**
	* @ORM\OneToMany(targetEntity="Problem", mappedBy="assignment_id")
	*/
	private $problems;
	
	public function __construct() {
		$this->problems = new ArrayCollection();
	}

	/**
	* @ORM\ManyToOne(targetEntity="Section")
	* @ORM\JoinColumn(name="section_id", referencedColumnName="id")
	*/
	private $section;

	/**
	* @ORM\Column(type="string", length=100)
	*/
	private $name;

	/**
	* @ORM\Column(type="string", length=255)
	*/
	private $description;

	/**
	* @ORM\Column(type="datetime")
	*/
	private $start_time;

	/**
	* @ORM\Column(type="datetime")
	*/
	private $end_time;

	/**
	* @ORM\Column(type="decimal", precision=12, scale=8)
	*/
	private $weight;

	/**
	* @ORM\Column(type="boolean")
	*/
	private $is_open;

	/**
	* @ORM\Column(type="boolean")
	*/
	private $is_extra_credit;


	# SETTERS
	public function setSection($sect){
		$this->section = $sect;
	}

	public function setName($nm){
		$this->name = $nm;
	}

	public function setDescription($desc){
		$this->description = $desc;
	}
	
	public function setStartTime($time){
		$this->start_time = $time;	
	}
	
	public function setEndTime($time){
		$this->end_time = $time;
	}

	public function setWeight($wght){
		$this->weight = $wght;
	}

	public function setIsOpen($open){
		$this->is_open = $open;
	}

	public function setIsExtraCredit($credit){
		$this->is_extra_credit = $credit;
	}

	
	# GETTERS
	public function getSection(){
		return $this->section;
	}

	public function getName(){
		return $this->name;
	}
	
	public function getDescription(){
		return $this->description;
	}

	public function getStartTime(){
		return $this->start_time;
	}

	public function getEndTime(){
		return $this->end_time;
	}

	public function getWeight(){
		return $this->weight;
	}

	public function getIsOpen(){
		return $this->is_open;
	}

	public function getIsExtraCredit(){
		return $this->is_extra_credit;
	}

}

?>
