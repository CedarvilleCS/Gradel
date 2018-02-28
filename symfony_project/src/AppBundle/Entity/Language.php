<?php

namespace AppBundle\Entity;

use JsonSerializable;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
*@ORM\Entity
*@ORM\Table(name="language")
**/
class Language implements JsonSerializable{
		
	public function __construct(){
		
		$a = func_get_args();
		$i = func_num_args();
		
		if(method_exists($this, $f='__construct'.$i)) {
			call_user_func_array(array($this,$f),$a);
		} else if($i != 0) {
			throw new Exception('ERROR: '.get_class($this).' constructor does not accept '.$i.' arguments');
		}
	}
		
	public function __construct4($nm, $ft, $ace, $def){
		$this->name = $nm;
		$this->filetype = $ft;
		$this->ace_mode = $ace;
		$this->default_code = $def;
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
	*@ORM\Column(type="string", length=255, unique=false)
	*/
	public $filetype;
	
	/**
	*@ORM\Column(type="string", length=255, unique=false)
	*/
	public $ace_mode;
	
	/**
	*@ORM\Column(type="blob", nullable = false)
	*/
	public $default_code;
	
	public function deblobinateDefaultCode(){			
		$val = stream_get_contents($this->default_code);
		rewind($this->default_code);
		
		return $val;
	}
	
	public function jsonSerialize(){
		return [
			'name' => $this->name,			
			'filetype' => $this->filetype,
			'ace_mode' => $this->ace_mode,
			'default_code' => $this->deblobinateDefaultCode(),
		];
	}
}
?>
