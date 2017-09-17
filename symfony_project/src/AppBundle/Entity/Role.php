<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="role")
 */
class Role{

	public function __construct(){
		
		$a = func_get_args();
		$i = func_num_args();
		
		if(method_exists($this, $f='__construct'.$i)) {
			call_user_func_array(array($this,$f),$a);
		} else if($i != 0) {
			throw new Exception('Contructor does not accept '.$i.' arguments');
		}
		
		$this->testcases = new ArrayCollection();
	}
	
	public function __construct1($role){
		$this->role_name = $role;
	}

	/** 
	* @ORM\Column(type="integer")
	* @ORM\Id
	* @ORM\GeneratedValue(strategy="AUTO")
	*/
	private $id;

	/**
	* @ORM\Column(type="string", length=255)
	*/
	private $role_name;

	# SETTERS
	public function setRoleName($name){
		$this->role_name = $name;
	}

	# GETTERS
	public function getRoleName(){
		return $this->role_name;
	}
}

?>
