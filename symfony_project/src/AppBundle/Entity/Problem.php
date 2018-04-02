<?php

namespace AppBundle\Entity;

use JsonSerializable;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * @ORM\Entity
 * @ORM\Table(name="problem")
 */
class Problem implements JsonSerializable{
	
	public function __construct(){
		
		$a = func_get_args();
		$i = func_num_args();
		
		if(method_exists($this, $f='__construct'.$i)) {
			call_user_func_array(array($this,$f),$a);
		} else if($i != 0) {
			throw new Exception('ERROR: '.get_class($this).' constructor does not accept '.$i.' arguments');
		} else {
		
			$this->version = 0;
			$this->testcase_counts = [null];
		}
		
		$this->testcases = new ArrayCollection();
		$this->problem_languages = new ArrayCollection();
		$this->slaves = new ArrayCollection();
		$this->queries = new ArrayCollection();
		
		$this->master = null;
	}
	
	public function __construct16($assign, $nm, $desc, $wght, $limit, $credit, $tot, $bef, $pen, $stop, $resp, $disp_tcr, $tc_lev, $disp_ext, $vers, $counts){
		$this->assignment = $assign;
		$this->name = $nm;
		$this->description = $desc;
		$this->weight = $wght;
		$this->time_limit = $limit;
		$this->is_extra_credit = $credit;
		
		$this->total_attempts = $tot;
		$this->attempts_before_penalty = $bef;
		$this->penalty_per_attempt = $pen;
		
		$this->stop_on_first_fail = $stop;
		$this->response_level = $resp;
		$this->display_testcaseresults = $disp_tcr;
		$this->testcase_output_level = $tc_lev;
		$this->extra_testcases_display = $disp_ext;
		
		$this->version = $vers;
		$this->testcase_counts = $counts;
	}
	
	# clone method override
	public function __clone(){
		
		if($this->id){
			$this->id = null;
			
			# clone the testcases
			$testcasesClone = new ArrayCollection();
			
			foreach($this->testcases as $testcase){
				$testcaseClone = clone $testcase;
				$testcaseClone->problem = $this;
				
				$testcasesClone->add($testcaseClone);
			}
			$this->testcases = $testcasesClone;
			
			
			# clone the problem_languages
			$plsClone = new ArrayCollection();			
			foreach($this->problem_languages as $pl){
				$plClone = clone $pl;
				$plClone->problem = $this;
				
				$plsClone->add($plClone);
			}
			$this->problem_languages = $plsClone;
			
			# clone the queries
			$queriesClone = new ArrayCollection();			
			foreach($this->queries as $qry){
				$qryClone = clone $qry;
				$qryClone->problem = $this;
				$qryClone->assignment = null;
				
				$queriesClone->add($qryClone);
			}
			$this->queries = $queriesClone;
			
			$this->slaves = new ArrayCollection();
			$this->master = null;			
		}
		
	}
	
	/** 
	* @ORM\Column(type="integer")
	* @ORM\Id
	* @ORM\GeneratedValue(strategy="AUTO")
	*/
	public $id;
	
	/**
	* @ORM\Column(type="integer")
	*/
	public $version;	
	
	/**
	* @ORM\Column(type="array")
	*/
	public $testcase_counts;
	
	/**
	* @ORM\OneToMany(targetEntity="Testcase", mappedBy="problem", cascade={"persist"})
	* @ORM\OrderBy({"seq_num" = "ASC"})
	*/
	public $testcases;
	
	/**
	* @ORM\OneToMany(targetEntity="Query", mappedBy="problem", cascade={"persist"})
	* @ORM\OrderBy({"timestamp" = "ASC"})
	*/
	public $queries;
	
	/**
    * @ORM\OneToMany(targetEntity="Problem", mappedBy="master", orphanRemoval=true)
    */
	public $slaves;
	
	/**
	* @ORM\ManyToOne(targetEntity="Problem", inversedBy="slaves")
	*/
	public $master;	 
		
	/**
	* @ORM\OneToMany(targetEntity="ProblemLanguage", mappedBy="problem", cascade={"persist"}, orphanRemoval=true)
	*/
	public $problem_languages;

	/**
	* @ORM\ManyToOne(targetEntity="Assignment", inversedBy="problems")
	* @ORM\JoinColumn(name="assignment_id", referencedColumnName="id", nullable = true, onDelete="CASCADE")
	*/
	public $assignment;

	/**
	* @ORM\Column(type="string", length=255)
	*/
	public $name;

	/**
	* @ORM\Column(type="text", nullable=false)
	*/
	public $description;

	
	/**
	* @ORM\Column(type="blob", nullable=true)
	*/
	public $custom_validator;
	
	public function deblobinateCustomValidator(){			
		$val = stream_get_contents($this->custom_validator);
		rewind($this->custom_validator);
		
		return $val;
	}
		
	/**
	* @ORM\Column(type="integer")
	*/
	public $weight;

	/**
	* @ORM\Column(type="integer")
	*/
	public $time_limit;

	/**
	* @ORM\Column(type="boolean")
	*/
	public $is_extra_credit;
	
	/**
	*@ORM\Column(type="integer")
	*/
	public $total_attempts;
	
	/**
	*@ORM\Column(type="integer")
	*/
	public $attempts_before_penalty;
	
	/**
	* @ORM\Column(type="decimal", precision=12, scale=8)
	*/
	public $penalty_per_attempt;
	
	/**
	* @ORM\Column(type="boolean")
	*/
	public $stop_on_first_fail;
	
	/**
	* @ORM\Column(type="string", length=10)
	*/
	public $response_level;
	
	/**
	* @ORM\Column(type="boolean")
	*/
	public $display_testcaseresults;
	
	/**
	* @ORM\Column(type="string", length=20)
	*/
	public $testcase_output_level;
	
	/**
	* @ORM\Column(type="boolean")
	*/
	public $extra_testcases_display;

	/**
	* @ORM\Column(type="boolean")
	*/
	public $allow_multiple = true;	
	
	/**
	* @ORM\Column(type="boolean")
	*/
	public $allow_upload = true;
	
	
	public function jsonSerialize(){
		return [
			'id' => $this->id,
			'name' => $this->name,
			'testcases' => $this->testcases->toArray(),
		];
	}
}

?>
