<?php

namespace AppBundle\Entity;

//use JsonSerializable;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * @ORM\Entity
 * @ORM\Table(name="semester")
 */
class Semester {
    public function __construct(){
        $a = func_get_args();
        $i = func_num_args();
        
        if(method_exists($this, $f='__construct'.$i)) {
            call_user_func_array(array($this,$f),$a);
        } else if($i != 0) {
            throw new Exception('ERROR: '.get_class($this).' constructor does not accept '.$i.' arguments');
        }
    }

    public function __construct3($year, $term, $is_current_semester){
        $this->year = $year;
        $this->term = $semester;
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
	* @ORM\Column(type="boolean")
	*/
	public $is_current_semester;
}

?>