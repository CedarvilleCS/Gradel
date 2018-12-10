<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Config\Definition\Exception\Exception;

use JsonSerializable;

/**
 * @ORM\Entity
 * @ORM\Table(name="semester")
 */
class Semester implements JsonSerializable {
    public function __construct(){
        $a = func_get_args();
        $i = func_num_args();
        
        if(method_exists($this, $f='__construct'.$i)) {
            call_user_func_array(array($this,$f),$a);
        } else if($i != 0) {
            throw new Exception('ERROR: '.get_class($this).' constructor does not accept '.$i.' arguments');
        }
    }

    public function __construct3($term, $year, $is_current_semester){
        $this->year = $year;
        $this->term = $term;
        $this->is_current_semester = $is_current_semester;
    }

    
    /** 
	* @ORM\Column(type="integer")
	* @ORM\Id
	* @ORM\GeneratedValue(strategy="AUTO")
	*/
    public $id;
    
    /**
	* @ORM\Column(type="string", length=255, unique=true)
	*/
    public $term;

    /**
	* @ORM\Column(type="integer")
	*/
    public $year;
    
    /**
	* @ORM\Column(type="boolean")
	*/
    public $is_current_semester;
    
    public function jsonSerialize(){
		return [
			'id' => $this->id,
			'term' => $this->term,
            'year' => $this->year,
            'is_current_semester' => $this->is_current_semester
		];
	}
}

?>