<?php

namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
*@ORM\Entity
*@ORM\Table(name="language")
**/
class Language
{

	/**
	*@ORM\Column(type="integer")
	*@ORM\Id
	*@ORM\GeneratedValue(strategy="AUTO")
	*/
	private $id;

	/**
	*@ORM\Column(type="string", length=255)
	*/
	private $name;
	
	#SETTERS
	public function setName($name) {
		$this->name = $name;
	}
	
	#GETTERS
	public function getName(){
		return $this->name;
	}
}
?>
