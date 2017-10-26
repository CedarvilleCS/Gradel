<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * @ORM\Entity
 * @ORM\Table(name="team", uniqueConstraints={@UniqueConstraint(name="team_name_assignment", columns={"name", "assignment_id"})})
 */
class Team{
	
	public function __construct(){
		
		$a = func_get_args();
		$i = func_num_args();
		
		if(method_exists($this, $f='__construct'.$i)) {
			call_user_func_array(array($this,$f),$a);
		} else if($i != 0) {
			throw new Exception('Contructor does not accept '.$i.' arguments');
		}
		
		$this->users = new ArrayCollection();
		$this->submissions = new ArrayCollection();
	}
	
	public function __construct2($nm, $assign){
		$this->name = $nm;
		$this->assignment = $assign;		
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
	* @ORM\ManyToOne(targetEntity="Assignment")
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
}

?>
