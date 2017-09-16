<?php

namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
*@ORM\Entity
*@ORM\Table(name="feedback")
**/
class Feedback
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
	private $short_response;

	/**
	*@ORM\Column(type="string", length=255)
	*/
	private $long_response;

	
	# SETTERS
	public function setShortResponse($sresp) {
		$this->short_response = $sresp;
	}
	
	public function setLongResponse($longo_response) {
		$this->long_response = $longo_response;
	}
	
	#GETTERZ
	public function getShortResponse(){
		return $this->short_response;
	}
	
	public function getLongResponse(){
		return $this->long_response;
	}
	
}
?>
