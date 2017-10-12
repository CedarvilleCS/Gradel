<?php

namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
*@ORM\Entity
*@ORM\Table(name="language")
**/
class Language {
		
	public function __construct(){
		
		$a = func_get_args();
		$i = func_num_args();
		
		if(method_exists($this, $f='__construct'.$i)) {
			call_user_func_array(array($this,$f),$a);
		} else if($i != 0) {
			throw new Exception('Contructor does not accept '.$i.' arguments');
		}
	}
		
	public function __construct1($nm){
		$this->name = $nm;
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
}
?>
