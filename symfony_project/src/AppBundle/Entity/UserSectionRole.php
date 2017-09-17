<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="usersectionrole")
 */
class UserSectionRole{
	
	public function __construct(){
		
		$a = func_get_args();
		$i = func_num_args();
		
		if(method_exists($this, $f='__construct'.$i)) {
			call_user_func_array(array($this,$f),$a);
		} else if($i != 0) {
			throw new Exception('Contructor does not accept '.$i.' arguments');
		}
	}
	
	public function __construct3($usr, $sect, $rl){
		$this->user = $usr;
		$this->section = $sect;
		$this->role = $rl;
	}	

	/**
	* @ORM\Id
	* @ORM\ManyToOne(targetEntity="User")
	* @ORM\JoinColumn(name="user_id", referencedColumnName="id")
	*/
	private $user;

	/**
	* @ORM\Id
	* @ORM\ManyToOne(targetEntity="Section")
	* @ORM\JoinColumn(name="section_id", referencedColumnName="id")
	*/
	private $section;

	/**
	* @ORM\Id
	* @ORM\ManyToOne(targetEntity="Role")
	* @ORM\JoinColumn(name="role_id", referencedColumnName="id")
	*/
	private $role;



	# SETTERS
	public function setUser($user){
		$this->user = $user;
	}

	public function setSection($section){
		$this->section = $section;
	}
	
	public function setRole($role){
		$this->role = $role;
	}

	
	
	# GETTERS
	public function getUser(){
		return $this->user;
	}

	public function getSection(){
		return $this->section;
	}

	public function getRole(){
		return $this->role;
	}



}

?>
