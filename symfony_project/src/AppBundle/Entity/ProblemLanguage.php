<?php

namespace AppBundle\Entity;

use AppBundle\Utils\Zipper;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * @ORM\Entity
 * @ORM\Table(name="problemlanguage")
 */
class ProblemLanguage{
	
	public function __construct(){
		
		$a = func_get_args();
		$i = func_num_args();
		
		if(method_exists($this, $f='__construct'.$i)) {
			call_user_func_array(array($this,$f),$a);
		} else if($i != 0) {
			throw new Exception('ERROR: '.get_class($this).' constructor does not accept '.$i.' arguments');
		}
	}
	
	public function __construct4($lang, $prob, $default, $comp){
		$this->language = $lang;
		$this->problem = $prob;
		$this->default_code = $default;
		$this->compilation_options = $comp;
	}
	
	# clone method override
	public function __clone(){
		
		if($this->id){
			$this->id = null;			
		}		
	}
	
	/** 
	* @ORM\Column(type="integer")
	* @ORM\Id
	* @ORM\GeneratedValue(strategy="AUTO")
	*/
	public $id;
	
	/**
	* @ORM\ManyToOne(targetEntity="Language")
	* @ORM\JoinColumn(name="language_id", referencedColumnName="id", onDelete="CASCADE")
	*/
	public $language;

	/**
	* @ORM\ManyToOne(targetEntity="Problem", inversedBy="problem_languages")
	* @ORM\JoinColumn(name="problem_id", referencedColumnName="id", onDelete="CASCADE")
	*/
	public $problem;
	
	
	/**
	* @ORM\Column(type="blob", nullable=true)
	*/
	public $default_code;
	
	public function getDefaultFileContents(){

		if($this->default_code == null){
			return [['name'=>'Main'.$this->language->filetype, 'contents'=>$this->language->deblobinateDefaultCode()]];
		}
				
		// get the contents of a submission file
		$temp = tmpfile();
		$temp_filename = stream_get_meta_data($temp)['uri'];
		
		if(file_put_contents($temp_filename, $this->default_code) === FALSE){
			return false;			
		}
		
		$zipper = new Zipper();		
		$contents = $zipper->getZipContents($temp_filename);
		
		if($contents === false){
			fseek($temp, 0);
			
			return [['name'=>'file'.$this->language->filetype, 'contents'=>fread($temp, filesize($temp_filename))]];
		}
		
		fclose($temp);

		return $contents;
	}


	public function deblobinateDefaultCode(){			
		$val = stream_get_contents($this->default_code);
		rewind($this->default_code);
		
		return $val;
	}

	/**
	* @ORM\Column(type="text", nullable=true)
	*/
	public $compilation_options;	
}

?>