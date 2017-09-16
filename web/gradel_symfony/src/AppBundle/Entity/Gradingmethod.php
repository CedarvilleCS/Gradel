<?php

namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
*@ORM\Entity
*@ORM\Table(name="gradingmethod")
**/
class Gradingmethod
{

	/**
	*@ORM\Column(type="integer")
	*@ORM\Id
	*@ORM\GeneratedValue(strategy="AUTO")
	*/
	private $id;

	/**
	*@ORM\Column(type="string", length=100)
	*/
	private $name;

	/**
	*@ORM\Column(type="string", length=255)
	*/
	private $description;
	
	# SETTERS
	public function setName($name) {
		$this->name = $name;
	}
	
	public function setDescription($desc) {
		$this->description = $desc;
	}
	
	# GETTERS
	public function getDescription(){
		return $this->description;
	}
	
	public function getName(){
		return $this->name;
	}

}
?>