<?php

namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
*@ORM\Entity
*@ORM\Table(name="assignmentgradingmethod")
**/
class AssignmentGradingMethod {
		
	public function __construct(){
		
		$a = func_get_args();
		$i = func_num_args();
		
		if(method_exists($this, $f='__construct'.$i)) {
			call_user_func_array(array($this,$f),$a);
		} else if($i != 0) {
			throw new Exception('ERROR: '.get_class($this).' constructor does not accept '.$i.' arguments');
		}
	}
	
	public function __construct1($pen){
		$this->penalty_per_day = $pen;
	}
	
	/**
	*@ORM\Column(type="integer")
	*@ORM\Id
	*@ORM\GeneratedValue(strategy="AUTO")
	*/
	public $id;
	
	
	/**
	* @ORM\Column(type="decimal", precision=12, scale=8)
	*/
	public $penalty_per_day;
	
}
?>