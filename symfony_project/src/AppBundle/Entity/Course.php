<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="course")
 */
class Course{
		
	public function __construct(){
		
		$a = func_get_args();
		$i = func_num_args();
		
		if(method_exists($this, $f='__construct'.$i)) {
			call_user_func_array(array($this,$f),$a);
		} else if($i != 0){
			throw new Exception('Contructor does not accept '.$i.' arguments');
		}
		
		$this->sections = new ArrayCollection();
	}
	
	public function __construct6($cd, $nm, $desc, $contest, $public, $deleted){
		$this->code = $cd;
		$this->name = $nm;
		$this->description = $desc;
		$this->is_contest = $contest;
		$this->is_public = $public;
		$this->is_deleted = $deleted;
	}


	/** 
	* @ORM\Column(type="integer")
	* @ORM\Id
	* @ORM\GeneratedValue(strategy="AUTO")
	*/
	public $id;

	/**
	* @ORM\OneToMany(targetEntity="Section", mappedBy="course")
	*/
	public $sections;

	/**
	* @ORM\Column(type="string", length=100)
	*/
	public $code;

	/**
	* @ORM\Column(type="string", length=100)
	*/
	public $name;

	/**
	* @ORM\Column(type="string", length=255)
	*/
	public $description;

	/**
	* @ORM\Column(type="boolean")
	*/
	public $is_contest;	
	
	/**
	* @ORM\Column(type="boolean")
	*/
	public $is_deleted;	
	
	/**
	* @ORM\Column(type="boolean")
	*/
	public $is_public;



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
	
	public function setIsDeleted($deleted){
		$this->is_deleted = $deleted;
	}
	
	public function setIsPublic($public){
		$this->is_public = $public;
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
		
	public function getIsDeleted(){
		return $this->is_deleted;
	}
	
	public function getIsPublic(){
		return $this->is_public;
	}

}

?>
