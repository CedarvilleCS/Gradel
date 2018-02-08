<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Config\Definition\Exception\Exception;
use \DateTime;

/**
 * @ORM\Entity
 * @ORM\Table(name="section")
 */
class Section{

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

	public function __construct8($crs, $nm, $sem, $yr, $start, $end, $public, $deleted){
		$this->course = $crs;
		$this->name = $nm;
		$this->semester = $sem;
		$this->year = $yr;
		$this->start_time = $start;
		$this->end_time = $end;
		$this->is_public = $public;
		$this->is_deleted = $deleted;
	}
	
	# clone method override
	public function __clone() {
		
		if($this->id){
			$this->id = null;
			
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

	public function isActive() {
		return $this->start_time < new DateTime("now") && $this->end_time > new DateTime("now");
	}
	

	/**
	* @ORM\Column(type="integer")
	* @ORM\Id
	* @ORM\GeneratedValue(strategy="AUTO")
	*/
	public $id;

	/**
	* @ORM\OneToMany(targetEntity="Assignment", mappedBy="section", cascade={"persist"})
	* @ORM\OrderBy({"id" = "ASC"})
	*/
	public $assignments;
	
	/**
     * @ORM\OneToMany(targetEntity="UserSectionRole", mappedBy="section", cascade={"persist"})
     */
    public $user_roles;

	/**
	* @ORM\ManyToOne(targetEntity="Course", inversedBy="sections")
	*/
	public $course;

	/**
	* @ORM\Column(type="string", length=255)
	*/
	public $name;

	/**
	* @ORM\Column(type="string", length=255)
	*/
	public $semester;

	/**
	* @ORM\Column(type="integer")
	*/
	public $year;

	/**
	* @ORM\Column(type="datetime")
	*/
	public $start_time;

	/**
	* @ORM\Column(type="datetime")
	*/
	public $end_time;

	/**
	* @ORM\Column(type="boolean")
	*/
	public $is_deleted;

	/**
	* @ORM\Column(type="boolean")
	*/
	public $is_public;
}

?>
