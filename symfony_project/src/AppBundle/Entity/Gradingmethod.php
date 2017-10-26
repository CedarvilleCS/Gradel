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
	*@ORM\Column(type="string", length=255, unique=true)
	*/
	public $name;

	/**
	*@ORM\Column(type="blob")
	*/
	public $description;
	
	public function deblobinateDescription(){			
		return stream_get_contents($this->description);
	}
}
?>