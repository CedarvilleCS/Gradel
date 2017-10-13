<?php

namespace AppBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 */
class User extends BaseUser{
		
	public function __construct(){
		
		$a = func_get_args();
		$i = func_num_args();
		
		if(method_exists($this, $f='__construct'.$i)) {
			call_user_func_array(array($this,$f),$a);
		} else if($i != 0){
			throw new Exception('Contructor does not accept '.$i.' arguments');
		}
		
	}
	
	public function __construct2($username, $email){
		
		parent::__construct();
		
		$this->username = $username;
		$this->username_canonical = $username;
		
		$this->email = $email;
		$this->email_canonical = $email;
		
		$this->enabled = true;
		
		$this->password = "N/A";
		
				
	}

	/** 
	* @ORM\Column(type="integer")
	* @ORM\Id
	* @ORM\GeneratedValue(strategy="AUTO")
	*/
	public $id;
	
	
	/** @ORM\Column(name="google_id", type="string", length=255, nullable=true) */
	protected $google_id;

	/** @ORM\Column(name="name", type="string", length=255, nullable=true) */
	protected $name;

	/** @ORM\Column(name="google_access_token", type="string", length=255, nullable=true) */
	protected $google_access_token;
	
	public function setGoogleId($googleID) {
		$this->google_id = $googleID;

		return $this;
	}

	public function getGoogleId() {
		return $this->google_id;
	}

	public function setName($name) {
		$this->name = $name;

		return $this;
	}

	public function getName() {
		return $this->name;
	}

	public function setGoogleAccessToken($googleAccessToken) {
		$this->google_access_token = $googleAccessToken;

		return $this;
	}

	public function getGoogleAccessToken() {
		return $this->google_access_token;
	}
}

?>
