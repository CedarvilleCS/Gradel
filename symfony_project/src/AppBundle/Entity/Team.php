<?php

namespace AppBundle\Entity;

use JsonSerializable;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * @ORM\Entity
 * @ORM\Table(name="team")
 */
class Team implements JsonSerializable{
	
	public function __construct(){
		
		$a = func_get_args();
		$i = func_num_args();
		
		if(method_exists($this, $f='__construct'.$i)) {
			call_user_func_array(array($this,$f),$a);
		} else if($i != 0) {
			throw new Exception('ERROR: '.get_class($this).' constructor does not accept '.$i.' arguments');
		}
		
		$this->users = new ArrayCollection();
		$this->submissions = new ArrayCollection();
	}
	
	public function __construct2($nm, $assign){
		$this->name = $nm;
		$this->assignment = $assign;		
	}
	
	# clone method override
	public function __clone(){
		
		if($this->id){
			$this->id = null;			
		}		
	}
	
	/** 
	* @ORM\Column(type="integer")
	* @ORM\Id
	* @ORM\GeneratedValue(strategy="AUTO")
	*/
	public $id;

	/**
	* @ORM\OneToMany(targetEntity="Submission", mappedBy="team")
	*/
	public $submissions;
	
	/**
	* @ORM\Column(type="string", length=255)
	*/
	public $name;

	/**
	* @ORM\ManyToOne(targetEntity="Assignment", inversedBy="teams")
	* @ORM\JoinColumn(name="assignment_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
	*/
	public $assignment;

	/**
	* @ORM\ManyToMany(targetEntity="User", inversedBy="teams")
	* @ORM\JoinTable(name="userteam",
	*	joinColumns={@ORM\JoinColumn(name="team_id", referencedColumnName="id", onDelete="CASCADE")},
	*	inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")}
	*	)
	*/
	public $users;
	
	
	public function jsonSerialize(){
		return [
			'name' => $this->name,			
			'users' => $this->users->toArray(),
		];
	}
}

?>
