<?php

namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Config\Definition\Exception\Exception;

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
			throw new Exception('ERROR: '.get_class($this).' constructor does not accept '.$i.' arguments');
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
	*@ORM\Column(type="blob")
	*/
	public $short_response;
	
	public function deblobinateShortResponse(){			
		$val = stream_get_contents($this->short_response);
		rewind($this->short_response);
		return $val;
	}

	/**
	*@ORM\Column(type="blob")
	*/
	public $long_response;
	
	public function deblobinateLongResponse(){			
		$val = stream_get_contents($this->long_response);
		rewind($this->long_response);
		return $val;
	}
	
}
?>
