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
	
	/** 
	* @ORM\Column(type="integer")
	* @ORM\Id
	* @ORM\GeneratedValue(strategy="AUTO")
	*/
	public $id;

	/**
	* @ORM\OneToMany(targetEntity="Submission", mappedBy="team")
	* @ORM\OrderBy({"timestamp" = "ASC"})
	*/
	public $submissions;
	
	/**
	* @ORM\Column(type="string", length=255)
	*/
	public $name;

	/**
	* @ORM\ManyToOne(targetEntity="Assignment", inversedBy="teams", cascade={"persist"})
	* @ORM\JoinColumn(name="assignment_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
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

	/**
	* @ORM\Column(type="integer") 
	*/
	public $workstation_number = 1;


	public function getMemberString(){

		$str = '';
		$first = true;
		foreach($this->users as $user){

			if(!$first){
				$str .= '\n'.$user->getFullName();
			} else {
				$str = $user->getFullName();
			}

			$first = false;
		}
		
		return $str;
	}
	
	public function jsonSerialize(){
		return [
			'name' => $this->name,			
			'users' => $this->users->toArray(),
			'id' => $this->id,
			'workstation_number' => $this->workstation_number,
			'member_string' => $this->getMemberString(),
		];
	}
}

?>
