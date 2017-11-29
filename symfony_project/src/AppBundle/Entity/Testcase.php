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

	public function __construct7($prob, $seq, $in, $out, $feed, $wght, $extra){
		$this->problem = $prob;
		$this->seq_num = $seq;
		$this->input = $in;
		$this->correct_output = $out;
		$this->feedback = $feed;
		$this->weight = $wght;
		$this->is_extra_credit = $extra;
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
	*@ORM\Column(type="blob")
	*/
	public $input;

	public function deblobinateInput(){
		$val = stream_get_contents($this->input);
		rewind($this->input);
		return $val;
	}

	/**
	*@ORM\Column(type="blob")
	*/
	public $correct_output;

	public function deblobinateCorrectOutput(){
		$val = stream_get_contents($this->correct_output);
		rewind($this->correct_output);
		return $val;
		
	}

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
