<?php

namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
*@ORM\Entity
*@ORM\Table(name="query")
**/
class Query {
	
	
	public function __construct(){
		
		$a = func_get_args();
		$i = func_num_args();	
		
		if(method_exists($this, $f='__construct'.$i)) {
			call_user_func_array(array($this,$f),$a);
		} else if($i != 0) {
			throw new Exception('ERROR: '.get_class($this).' constructor does not accept '.$i.' arguments');
		} else {
			$this->timestamp = new \DateTime("now");
		}
	}
	
	public function __construct4($reference, $quest, $ans, $time){
		
		if(get_class($reference) == "AppBundle\Entity\Assignment"){
			$this->assignment = $reference;
		} else if(get_class($reference) == "AppBundle\Entity\Problem"){
			$this->problem = $reference;
		} else {
			throw new Exception('ERROR: '.get_class($reference).' is not a valid class for reference');
		}		
		
		$this->question = $quest;
		$this->answer = $ans;
		$this->timestamp = $time;
	}
	
	/** 
	* @ORM\Column(type="integer")
	* @ORM\Id
	* @ORM\GeneratedValue(strategy="AUTO")
	*/
	public $id;
	
	/**
     * @ORM\ManyToOne(targetEntity="Problem", inversedBy="queries")
     * @ORM\JoinColumn(name="problem_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
	public $problem;
	
	
	/**
     * @ORM\ManyToOne(targetEntity="Assignment", inversedBy="queries")
     * @ORM\JoinColumn(name="assignment_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
	public $assignment;
	
	/**
	* @ORM\Column(type="text", nullable=true)
	*/	
	public $question;
	
	/**
	* @ORM\Column(type="text", nullable=true)
	*/	
	public $answer;
	
	/**
	* @ORM\Column(type="datetime")
	*/
	public $timestamp;
	
}

?>