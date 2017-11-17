<?php

namespace AppBundle\Utils;

use AppBundle\Entity\Role;
use AppBundle\Entity\User;
use AppBundle\Entity\Team;
use AppBundle\Entity\Course;
use AppBundle\Entity\Section;
use AppBundle\Entity\Assignment;
use AppBundle\Entity\Problem;
use AppBundle\Entity\ProblemLanguage;
use AppBundle\Entity\UserSectionRole;
use AppBundle\Entity\Testcase;
use AppBundle\Entity\Submission;
use AppBundle\Entity\Language;
use AppBundle\Entity\ProblemGradingMethod;
use AppBundle\Entity\AssignmentGradingMethod;
use AppBundle\Entity\Feedback;
use AppBundle\Entity\TestcaseResult;

use \DateTime;

use Symfony\Component\Config\Definition\Exception\Exception;

class Uploader  {
	
	public $web_directory;
	
	public function __construct($web_directory) {
		
		if(!$web_directory){
			throw new Exception('The constructor requires the web directory to be provided.');
		}
		
		$this->web_directory = $web_directory;		
	}
	
	# returns the contents of the file base64-encoded and the name of the file
	public function getFileContents($file){

		# Get the file contents and name
		$fileContents = base64_encode(file_get_contents($file["tmp_name"]));
		$fileName = basename($file["name"]);

		# return an array of the contents and name
		return ["contents" => $fileContents, "name" => $fileName];
	}
	
	public function getUploadDirectory($user, $problem){
		
		$target_directory = $this->web_directory."compilation/uploads/".$user->id."/".$problem->id."/";
		
		# clear out the uploads directory and rebuild it
		shell_exec("rm -rf ".$target_directory);
		shell_exec("mkdir -p ".$target_directory);
		
		return $target_directory;
	}
	
	public function uploadSubmissionFile($file, $user, $problem){
		
		# file paths
		$target_directory = $this->getUploadDirectory($user, $problem);
		$target_file = $target_directory.$file["name"];		
		
		# check to see if the file was uploaded
		if(move_uploaded_file($file["tmp_name"], $target_file)){
			return $target_file;
			
		} else {
			return null;		
		}		
	}	
	
}










?>