<?php

namespace AppBundle\Entity;

use JsonSerializable;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * @ORM\Entity
 * @ORM\Table(name="leaderboard")
 */
class Leaderboard {
    
    public function __construct(){
        
        $this->board = null;
        $this->contest = null;
    }

    /** 
	* @ORM\Column(type="integer")
	* @ORM\Id
	* @ORM\GeneratedValue(strategy="AUTO")
	*/
	public $id;
    	
	/**
	* @ORM\OneToOne(targetEntity="Assignment", mappedBy="leaderboard")
	*/
    public $contest;

    /**
	* @ORM\Column(type="text")
	*/
    public $board_elevated;
    
    /**
	* @ORM\Column(type="text")
	*/
    public $board;

}

?>