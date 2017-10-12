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
	* @ORM\Column(type="text")
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

}

?>
