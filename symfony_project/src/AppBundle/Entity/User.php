<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 */
class User{
		
	public function __construct(){
		
		$a = func_get_args();
		$i = func_num_args();
		
		if(method_exists($this, $f='__construct'.$i)) {
			call_user_func_array(array($this,$f),$a);
		} else if($i != 0){
			throw new Exception('Contructor does not accept '.$i.' arguments');
		}
	}
	
	public function __construct5($first, $last, $mail, $login, $level){
		$this->first_name = $first;
		$this->last_name = $last;
		$this->email = $mail;
		$this->last_login = $login;
		$this->access_level = $level;
	}

	/** 
	* @ORM\Column(type="integer")
	* @ORM\Id
	* @ORM\GeneratedValue(strategy="AUTO")
	*/
	private $id;

	/**
	* @ORM\Column(type="string", length=100)
	*/
	private $first_name;

	/**
	* @ORM\Column(type="string", length=100)
	*/
	private $last_name;

	/**
	* @ORM\Column(type="string", length=100)
	*/
	private $email;

	/**
	* @ORM\Column(type="datetime")
	*/
	private $last_login;

	/**
	* @ORM\ManyToOne(targetEntity="Role")
	* @ORM\JoinColumn(name="access_level", referencedColumnName="id")
	*/
	private $access_level;



	# SETTERS
	public function setFirstName($first){
		$this->first_name = $first;
	}

	public function setLastName($last){
		$this->last_name = $last;
	}

	public function setEmail($email){
		$this->email = $email;
	}

	public function setAccessLevel($role){
		$this->access_level = $role;
	}

	public function setLastLogin($time){
		$this->last_login = $time; 
	}

	public function updateLastLogin(){
		$this->last_login = new \DateTime("now");
	}

	
		
	# GETTERS
	public function getFirstName(){
		return $this->first_name;
	}

	public function getLastName(){
		return $this->last_name;
	}

	public function getEmail(){
		return $this->email;
	}

	public function getAccessLevel(){
		return $this->access_level;
	}

	public function getLastLogin($time){
		return $this->last_login; 
	}

}

?>
