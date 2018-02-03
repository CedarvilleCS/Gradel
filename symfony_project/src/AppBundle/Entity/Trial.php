<?php

namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
*@ORM\Entity
*@ORM\Table(name="trial")
**/
class Trial {	
	
	public function __construct(){
		
		$a = func_get_args();
		$i = func_num_args();	
		
		if(method_exists($this, $f='__construct'.$i)) {
			call_user_func_array(array($this,$f),$a);
		} else if($i != 0) {
			throw new Exception('ERROR: '.get_class($this).' constructor does not accept '.$i.' arguments');
		}
	}
	
	public function __construct9($prob, $user, $file, $name, $language, $main, $package, $edit_time, $show){
	
		$this->problem = $prob;
		$this->user = $user;
		$this->file = $file;
		$this->filename = $filename;
		$this->language = $language;
		$this->main_class = $main;
		$this->package_name = $package;
		$this->last_edit_time = $edit_time;
		
		$this->show_description = $show;
		
	}

	/**
	*@ORM\Column(type="integer")
	*@ORM\Id
	*@ORM\GeneratedValue(strategy="AUTO")
	*/
	public $id;
	
	/**
     * @ORM\ManyToOne(targetEntity="Problem")
     * @ORM\JoinColumn(name="problem_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
	public $problem;	

	/**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
	public $user;	

	/**
	* @ORM\Column(type="blob", nullable=false)
	*/
	public $file;
	
	public function deblobinateFile(){			
		$val = stream_get_contents($this->file);
		rewind($this->file);
		return $val;
	}
	
	/**
	* @ORM\Column(type="string", length=255)
	*/
	public $filename;
	
	/**
	* @ORM\ManyToOne(targetEntity="Language")
	* @ORM\JoinColumn(name="language_id", referencedColumnName="id", nullable=false)
	*/
	public $language;
	
	/**
	* @ORM\Column(type="string", length=255)
	*/
	public $main_class;
	
	/**
	* @ORM\Column(type="string", length=255)
	*/
	public $package_name;
	
	/**
	*@ORM\Column(type="datetime")
	*/
	public $last_edit_time;	
	
	/**
	*@ORM\Column(type="boolean")
	*/
	public $show_description;
}


?>