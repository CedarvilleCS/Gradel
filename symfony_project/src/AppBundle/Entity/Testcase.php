<?php

namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Config\Definition\Exception\Exception;

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
			throw new Exception('ERROR: '.get_class($this).' constructor does not accept '.$i.' arguments');
		}
	}

	public function __construct8($prob, $seq, $in, $cmd, $out, $feed, $wght, $extra){
		$this->problem = $prob;
		$this->seq_num = $seq;
		$this->input = $in;
		$this->command_line_input = $cmd;
		$this->correct_output = $out;
		$this->feedback = $feed;
		$this->weight = $wght;
		$this->is_extra_credit = $extra;
	}
	
	# clone method override
	public function __clone(){
		
		if($this->id){
			$this->id = null;	
		}
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
     * @ORM\JoinColumn(name="problem_id", referencedColumnName="id", onDelete="CASCADE")
     */
	public $problem;

	/**
	*@ORM\Column(type="integer")
	*/
	public $seq_num;

	/**
	*@ORM\Column(type="text", nullable=true)
	*/
	public $input;
	
	/**
	*@ORM\Column(type="text", nullable=true)
	*/
	public $command_line_input;

	/**
	*@ORM\Column(type="text")
	*/
	public $correct_output;

	/**
     * Multiple FB per TC
     * @ORM\ManyToOne(targetEntity="Feedback", cascade={"persist"})
     * @ORM\JoinColumn(name="feedback_id", referencedColumnName="id")
     */
	public $feedback;

	/**
	*@ORM\Column(type="integer")
	*/
	public $weight;
	
	/**
	*@ORM\Column(type="boolean")
	*/
	public $is_extra_credit;
}
?>
