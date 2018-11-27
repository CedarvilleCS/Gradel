<?php

namespace AppBundle\Entity;

use JsonSerializable;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Config\Definition\Exception\Exception;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 */
class User extends BaseUser implements JsonSerializable{

	public function __construct(){

		$a = func_get_args();
		$i = func_num_args();

		if(method_exists($this, $f='__construct'.$i)) {
			call_user_func_array(array($this,$f),$a);
		} else if($i != 0){
			throw new Exception('ERROR: '.get_class($this).' constructor does not accept '.$i.' arguments');
		}
		
		$this->teams = new ArrayCollection();
		$this->section_roles = new ArrayCollection();
	}

	public function __construct2($username, $email){

		parent::__construct();

		$this->username = $username;
		$this->username_canonical = $username;

		$this->email = $email;
		$this->email_canonical = $email;

		$this->enabled = true;

		$this->salt = null;
		$this->password = "N/A";


	}

	public function __construct6($username, $email, $google_id, $google_access_token, $first_name, $last_name){

		parent::__construct();

		$this->username = $username;
		$this->username_canonical = $username;

		$this->email = $email;
		$this->email_canonical = $email;

		$this->enabled = true;

		$this->salt = null;
		$this->password = "N/A";

		$this->google_id = $google_id;
		$this->google_access_token = $google_access_token;

		$this->first_name = $first_name;
		$this->last_name = $last_name;
	}


	/**
	* @ORM\Column(type="integer")
	* @ORM\Id
	* @ORM\GeneratedValue(strategy="AUTO")
	*/
	public $id;

	/** @ORM\Column(name="last_name", type="string", length=255, nullable=true) */
	protected $last_name;

	/** @ORM\Column(name="first_name", type="string", length=255, nullable=true) */
	protected $first_name;

	/** @ORM\Column(name="google_id", type="string", length=255, nullable=true) */
	protected $google_id;

	/** @ORM\Column(name="google_access_token", type="string", length=255, nullable=true) */
	protected $google_access_token;

	public function setGoogleId($googleID) {
		$this->google_id = $googleID;

		return $this;
	}

	public function getGoogleId() {
		return $this->google_id;
	}

	public function getID() {
		return $this->id;
	}

	public function getFullName(){
		
		$first_name = ($this->first_name) ? $this->first_name : $this->email;
		$last_name = ($this->last_name) ? $this->last_name : "";

		return trim($first_name." ".$last_name);
	}

	public function setFirstName($first_name) {
		$this->first_name = $first_name;

		return $this;
	}

	public function getFirstName() {
		
		if($this->first_name != ''){
			return $this->first_name;
		} else {
			return $this->email;
		}
	}

	public function setLastName($last_name) {
		$this->last_name = $last_name;

		return $this;
	}

	public function getLastName() {
		if($this->last_name != ''){
			return $this->last_name;
		} else {
			return '';
		}
	}

	public function setGoogleAccessToken($googleAccessToken) {
		$this->google_access_token = $googleAccessToken;

		return $this;
	}

	public function getGoogleAccessToken() {
		return $this->google_access_token;
	}

	/**
	* @ORM\ManyToMany(targetEntity="Team", mappedBy="users")
	*/
	public $teams;
	
	/**
     * @ORM\OneToMany(targetEntity="UserSectionRole", mappedBy="user")
     */
	public $section_roles;
	
	public function __toString() {
		return $this->getFullName();
	}
	
	public function jsonSerialize() {
		
		$first_name = ($this->first_name) ? $this->first_name : $this->email;
		$last_name = ($this->last_name) ? $this->last_name : "";
		
		return [
			'id' => $this->id,
			'first_name' => $first_name,
			'last_name' => $last_name,
			'email' => $this->email,
			'full_name' => $this->getFullName(),
		];
	}
}

?>
