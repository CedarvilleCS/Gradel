<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="course")
 */
class Course{

	/** 
	* @ORM\Column(type="integer")
	* @ORM\Id
	* @ORM\GeneratedValue(strategy="AUTO")
	*/
	private $id;

	/**
	* @ORM\OneToMany(targetEntity="Section", mappedBy="course_id")
	*/
	private $sections;

	public function __construct() {
		$this->sections = new ArrayCollection();
	}	

	/**
	* @ORM\Column(type="string", length=100)
	*/
	private $code;

	/**
	* @ORM\Column(type="string", length=100)
	*/
	private $name;

	/**
	* @ORM\Column(type="string", length=255)
	*/
	private $description;

	/**
	* @ORM\Column(type="boolean")
	*/
	private $is_contest;



	# SETTERS
	public function setCode($cd){
		$this->code = $cd;
	}

	public function setName($nm){
		$this->name = $nm;
	}

	public function setDescription($desc){
		$this->description = $desc;
	}

	public function setIsContest($contest){
		$this->is_contest = $contest;
	}

	
	# GETTERS
	public function getSections(){
		return $this->sections;
	}

	public function getCode(){
		return $this->code;
	}
	
	public function getName(){
		return $this->name;
	}

	public function getDescription(){
		return $this->description;
	}

	public function getIsContest(){
		return $this->is_contest;
	}

}

?>
