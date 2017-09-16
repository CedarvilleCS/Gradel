<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="role")
 */
class Role{

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
