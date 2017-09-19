<?php

namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
*@ORM\Entity
*@ORM\Table(name="feedback")
**/
class Feedback {
		
	public function __construct(){
		
		$a = func_get_args();
		$i = func_num_args();
		
		if(method_exists($this, $f='__construct'.$i)) {
			call_user_func_array(array($this,$f),$a);
		} else if($i != 0){
			throw new Exception('Contructor does not accept '.$i.' arguments');
		}
	}
	
	public function __construct2($short, $long){
		$this->short_response = $short;
		$this->long_response = $long;
	}

	/**
	*@ORM\Column(type="integer")
	*@ORM\Id
	*@ORM\GeneratedValue(strategy="AUTO")
	*/
	public $id;

	/**
	*@ORM\Column(type="string", length=100)
	*/
	public $short_response;

	/**
	*@ORM\Column(type="string", length=255)
	*/
	public $long_response;

	
	# SETTERS
	public function setShortResponse($sresp) {
		$this->short_response = $sresp;
	}
	
	public function setLongResponse($longo_response) {
		$this->long_response = $longo_response;
	}
	
	# GETTERS
	public function getShortResponse(){
		return $this->short_response;
	}
	
	public function getLongResponse(){
		return $this->long_response;
	}
	
}
?>
