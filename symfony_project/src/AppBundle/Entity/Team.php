<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctring\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="team")
 */
class Team{
	/** 
	* @ORM\Column(type="integer")
	* @ORM\Id
	* @ORM\GeneratedValue(strategy="AUTO")
	*/
	private $id;

	/**
	* @ORM\OneToMany(targetEntity="Submission", mappedBy="team_id")
	*/
	private $submissions;
	
	/**
	* @ORM\Column(type="string", length=50)
	*/
	private $name;

	/**
	* @ORM\ManyToOne(targetEntity="Assignment")
	* @ORM\JoinColumn(name="assignment_id", referencedColumnName="id")
	*/
	private $assignment;


	/**
	* @ORM\ManyToMany(targetEntity="User")
	* @ORM\JoinTable(name="userteam",
	*	joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
	*	inverseJoinColumns={@ORM\JoinColumn(name="team_id", referencedColumnName="id")}
	*	)
	*/
	private $users;

	public function __construct() {
		$this->users = new \Doctrine\Common\Collections\ArrayCollection();
		$this->submissions = new ArrayCollection();
	}
	
	public function addUserToTeam($newuser){
		$users[] = $newuser;
	}
	
	# SETTERS
	public function setName($nm) {
		$this->name = $nm;
	}
	
	public function setAssignment($assig) {
		$this->assignment = $assig;
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
