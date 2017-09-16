<?php

namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
*@ORM\Entity
*@ORM\Table(name="filetype")
**/
class Filetype
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
	private $extension;

	#SETTERS
	public function setExtension($ext) {
		$this->extension = $ext;
	}
	
	#GETTERS
	public function getExtension(){
		return $this->extension;
	}
	
}
?>