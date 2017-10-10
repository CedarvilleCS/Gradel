<?php

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Security\Core\User\UserInterface;

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
	public $id;

	/**
	* @ORM\Column(type="string", length=255)
	*/
	public $first_name;

	/**
	* @ORM\Column(type="string", length=255)
	*/
	public $last_name;

	/**
	* @var string $email
	*
	* @ORM\Column(name="email", type="string", length=255, unique=true)
	*/
	public $email;

	/**
	* @ORM\Column(type="datetime")
	*/
	public $last_login;

	/**
	* @ORM\ManyToOne(targetEntity="Role")
	* @ORM\JoinColumn(name="access_level", referencedColumnName="id")
	*/
	public $access_level;
	
    public function getUsername()
    {
        return $this->email;
    }

    public function getSalt()
    {
        return null;
    }

    public function getPassword()
    {
        return null;
    }

    public function getRoles()
    {
        return $this->access_level;
    }

    public function eraseCredentials()
    {
    }

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->email,
            $this->first_name,
			$this->last_name,
            // see section on salt below
            // $this->salt,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->email,
            $this->first_name,
			$this->last_name,
            // see section on salt below
            // $this->salt
        ) = unserialize($serialized);
    }

}

?>
