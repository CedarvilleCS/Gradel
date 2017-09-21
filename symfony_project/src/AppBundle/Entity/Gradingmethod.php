<?php

namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
*@ORM\Entity
*@ORM\Table(name="gradingmethod")
**/
class Gradingmethod {
		
	public function __construct(){
		
		$a = func_get_args();
		$i = func_num_args();
		
		if(method_exists($this, $f='__construct'.$i)) {
			call_user_func_array(array($this,$f),$a);
		} else if($i != 0) {
			throw new Exception('Contructor does not accept '.$i.' arguments');
		}
	}
	
	public function __construct2($nm, $desc){
		$this->name = $nm;
		$this->description = $desc;
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
	public $name;

	/**
	*@ORM\Column(type="text")
	*/
	public $description;
	
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