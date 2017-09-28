<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctring\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="team")
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
		$this->name = $name;
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
	* @ORM\Column(type="string", length=50)
	*/
	public $name;

	/**
	* @ORM\ManyToOne(targetEntity="Assignment")
	* @ORM\JoinColumn(name="assignment_id", referencedColumnName="id")
	*/
	public $assignment;

	/**
	* @ORM\ManyToMany(targetEntity="User")
	* @ORM\JoinTable(name="userteam",
	*	joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
	*	inverseJoinColumns={@ORM\JoinColumn(name="team_id", referencedColumnName="id")}
	*	)
	*/
	public $users;

	
	# SETTERS
	public function setName($nm) {
		$this->name = $nm;
	}
	
	public function setAssignment($assig) {
		$this->assignment = $assig;
	}
	
	public function addUserToTeam($newuser){
		$users[] = $newuser;
	}
	
	#GETTERZ
	public function getName(){
		return $this->name;
	}
	
	public function getAssignment(){
		return $this->assignment;
	}
	
	public function getUsers(){
		return $this->users;
	}
	
	public function getSubmissions(){
		return $this->submissions;
	}
}

?>
