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
	* @ORM\ManyToOne(targetEntity="User", cascade={"persist", "remove"})
	* @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
	*/
	public $user;

	/**
	* @ORM\Id
	* @ORM\ManyToOne(targetEntity="Section", cascade={"persist", "remove"})
	* @ORM\JoinColumn(name="section_id", referencedColumnName="id", onDelete="CASCADE")
	*/
	public $section;

	/**
	* @ORM\Id
	* @ORM\ManyToOne(targetEntity="Role", cascade={"persist", "remove"})
	* @ORM\JoinColumn(name="role_id", referencedColumnName="id", onDelete="CASCADE")
	*/
	public $role;
	
}
?>
