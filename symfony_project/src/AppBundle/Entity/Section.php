<?php

namespace AppBundle\Entity;

use JsonSerializable;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Config\Definition\Exception\Exception;
use \DateTime;

/**
 * @ORM\Entity
 * @ORM\Table(name="section")
 */
class Section implements JsonSerializable{

	public function __construct(){
		$this->assignments = new ArrayCollection();
		$this->user_roles = new ArrayCollection();
		$this->slaves = new ArrayCollection();
	}
		
	public function jsonSerialize(){
		return [
			'name' => $this->name,			
			'assignments' => $this->assignments->toArray(),
			'user_roles' => $this->user_roles->toArray(),

			'takers' => $this->getTakers(),
		];
	}
	
	# clone method override
	public function __clone() {
		
		if($this->id){
			$this->id = null;

			$this->slaves = new ArrayCollection();
			$this->master = null;
			
			$this->name = $this->name." CLONE";
			
			# clone assignments
			$assignmentsClone = new ArrayCollection();
			
			foreach($this->assignments as $assignment){
				$assignmentClone = clone $assignment;
				$assignmentClone->section = $this;
				
				$assignmentsClone->add($assignmentClone);
			}
			$this->assignments = $assignmentsClone;
			
			
			# clone user roles
			$usrsClone = new ArrayCollection();
			
			foreach($this->user_roles as $usr){
				
				if($usr->role->role_name == "Teaches"){
				
					$usrClone = clone $usr;
					$usrClone->section = $this;
					
					$usrsClone->add($usrClone);
				}
			}
			$this->user_roles = $usrsClone;			
		}		
	}
	
	public function isActive(){
		
		$currTime = new \DateTime("now");
		
		return $this->start_time <= $currTime && $currTime < $this->end_time;
  	}  

	public function getAllUsers(){

		$users = [];

		foreach($this->user_roles as $usr){
			$users[] = $usr->user;
		}

		return $users;
	}

	public function getAllProblems(){

		$allProbs = [];

		foreach($this->assignments->toArray() as $asgn){
			foreach($asgn->problems->toArray() as $prob){
				$allProbs[] = $prob;
			}
		}

		return $allProbs;
	}

	public function getRegularUsers(){

		$users = [];
	
		foreach($this->user_roles as $usr){
		  if($usr->role->role_name == 'Takes'  && !$usr->user->hasRole("ROLE_SUPER")){
			  $users[] = $usr->user;
			}
		}
	
		return $users;
	}	
		  
	public function getElevatedUsers(){
			
		$users = [];

		foreach($this->user_roles as $usr){
			if($usr->role->role_name == 'Judges' || $usr->role->role_name == 'Teaches' || $usr->user->hasRole("ROLE_SUPER")){
				$users[] = $usr->user;
			}
		}

		return $users;  
	}

	public function getJudgeUsers(){

		$judges = [];

		foreach($this->user_roles as $usr){
			if($usr->role->role_name == 'Judges'){
				$judges[] = $usr->user;
			}
		}

		return $judges;
	}

	public function getTakers(){

		$takers = [];

		foreach($this->user_roles as $usr){
			if($usr->role->role_name == 'Takes'){
				$takers[] = $usr->user;
			}
		}

		return $takers;
	}

	public function getTeachers(){

		$teachers = [];

		foreach($this->user_roles as $usr){
			if($usr->role->role_name == 'Teaches'){
				$teachers[] = $usr->user;
			}
		}

		return $teachers;
	}

	public function getTakerCSV(){
		$arr = $this->getTakers();

		foreach($arr as &$a){
			$a = $a->getEmail();
		}

		return $arr;
	}

	public function getTeacherCSV(){
		$arr = $this->getTeachers();

		foreach($arr as &$a){
			$a = $a->getEmail();
		}

		return $arr;
	}

	/**
	* @ORM\Column(type="integer")
	* @ORM\Id
	* @ORM\GeneratedValue(strategy="AUTO")
	*/
	public $id;
	
	/**
	* @ORM\ManyToOne(targetEntity="Section", inversedBy="slaves")
	*/
	public $master = null;	 

	/**
    * @ORM\OneToMany(targetEntity="Section", mappedBy="master")
    */
	public $slaves;

	/**
	* @ORM\OneToMany(targetEntity="Assignment", mappedBy="section", cascade={"persist"})
	* @ORM\OrderBy({"start_time" = "ASC", "id" = "ASC"})
	*/
	public $assignments;
	
	/**
	* @ORM\OneToMany(targetEntity="UserSectionRole", mappedBy="section", cascade={"persist", "remove"}, orphanRemoval=true)
	*/
	public $user_roles;

	/**
	* @ORM\ManyToOne(targetEntity="Course", inversedBy="sections")
	*/
	public $course = null;

	/**
	* @ORM\Column(type="string", length=255)
	*/
	public $name = "";

	/**
	* @ORM\Column(type="string", length=255)
	*/
	public $semester = "";

	/**
	* @ORM\Column(type="integer")
	*/
	public $year = 0;

	/**
	* @ORM\Column(type="datetime")
	*/
	public $start_time = null;

	/**
	* @ORM\Column(type="datetime")
	*/
	public $end_time = null;

	/**
	* @ORM\Column(type="boolean")
	*/
	public $is_deleted = false;
}

?>
