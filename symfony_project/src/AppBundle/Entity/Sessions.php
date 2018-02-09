<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * @ORM\Entity
 * @ORM\Table(name="sessions")
 */
class Session{

	public function __construct(){

		$a = func_get_args();
		$i = func_num_args();

		if(method_exists($this, $f='__construct'.$i)) {
			call_user_func_array(array($this,$f),$a);
		} else if($i != 0){
			throw new Exception('ERROR: '.get_class($this).' constructor does not accept '.$i.' arguments');
		}

		$this->assignments = new ArrayCollection();
	}

	/**
  * @ORM\Id
	* @ORM\Column(type="string")
	*/
  public $sess_id;
  
	/**
	* @ORM\Column(type="blob")
	*/
  public $sess_data;

	/**
	* @ORM\Column(type="integer")
	*/
  public $sess_time;
  
	/**
	* @ORM\Column(type="integer")
	*/
	public $sess_lifetime;

}

?>
