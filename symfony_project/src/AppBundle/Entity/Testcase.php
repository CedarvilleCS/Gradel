<?php

namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
*@ORM\Entity
*@ORM\Table(name="testcase")
**/
class Testcase {
	
	public function __construct(){
		
		$a = func_get_args();
		$i = func_num_args();
		
		if(method_exists($this, $f='__construct'.$i)) {
			call_user_func_array(array($this,$f),$a);
		} else if($i != 0) {
			throw new Exception('Contructor does not accept '.$i.' arguments');
		}
	}
	
	public function __construct6($prob, $seq, $in, $out, $feed, $wght){
		$this->problem = $prob;
		$this->seq_num = $seq;
		$this->input = $in;
		$this->correct_output = $out;
		$this->feedback = $feed;
		$this->weight = $wght;
	}

	/**
	*@ORM\Column(type="integer")
	*@ORM\Id
	*@ORM\GeneratedValue(strategy="AUTO")
	*/
	public $id;

	/**
     * Multiple TC per Problem
     * @ORM\ManyToOne(targetEntity="Problem", inversedBy="testcases")
     * @ORM\JoinColumn(name="problem_id", referencedColumnName="id")
     */
	public $problem;
	
	/**
	*@ORM\Column(type="integer")
	*/
	public $seq_num;

	/**
	*@ORM\Column(type="blob")
	*/
	public $input;
	
	public function deblobinateInput(){			
		return stream_get_contents($this->input);
	}
	
	/**
	*@ORM\Column(type="blob")
	*/
	public $correct_output;
	
	public function deblobinateCorrectOutput(){			
		return stream_get_contents($this->correct_output);
	}

	/**
     * Multiple FB per TC
     * @ORM\ManyToOne(targetEntity="Feedback")
     * @ORM\JoinColumn(name="feedback_id", referencedColumnName="id")
     */
	public $feedback;
	
	/**
	*@ORM\Column(type="decimal", precision=9, scale=8)
	*/
	public $weight;
}
?>