<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * @ORM\Entity
 * @ORM\Table(name="course")
 */
class Course{
		
	public function __construct(){
		$this->sections = new ArrayCollection();
	}
	
	/** 
	* @ORM\Column(type="integer")
	* @ORM\Id
	* @ORM\GeneratedValue(strategy="AUTO")
	*/
	public $id;

	/**
	* @ORM\OneToMany(targetEntity="Section", mappedBy="course")
	* @ORM\OrderBy({"id" = "ASC"})
	*/
	public $sections = null;

	/**
	* @ORM\Column(type="string", length=100)
	*/
	public $code = "ABC-123";

	/**
	* @ORM\Column(type="string", length=100)
	*/
	public $name = "Unnamed Course";

	/**
	* @ORM\Column(type="text")
	*/
	public $description = "This course is a default course";

	/**
	* @ORM\Column(type="boolean")
	*/
	public $is_contest = false;	
	
	/**
	* @ORM\Column(type="boolean")
	*/
	public $is_deleted = false;
	
	public static function cmp($a, $b){
		
		if($a->code == ''){
			return 1;
		} 
		
		if($b->code == ''){
			return -1;
		}
		
		return strcmp($a->code, $b->code);
	}
}

?>
