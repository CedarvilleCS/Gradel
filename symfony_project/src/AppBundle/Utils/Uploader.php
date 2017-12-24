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
use AppBundle\Entity\AssignmentGradingMethod;
use AppBundle\Entity\Feedback;
use AppBundle\Entity\TestcaseResult;

use \DateTime;

use Symfony\Component\Config\Definition\Exception\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class Uploader  {
	
	public $web_directory;
	
	public function __construct($web_directory) {
		
		if(!$web_directory){
			throw new Exception('The constructor requires the web directory to be provided.');
		}		
		$this->web_directory = $web_directory;		
	}
	
	/*
		This function returns the contents of the file as
		base64-encoded string and the name of the file
	*/
	public function getFileContents($file){

		# Get the file contents and name
		$fileContents = base64_encode(file_get_contents($file["tmp_name"]));
		$fileName = basename($file["name"]);
		
				
		if(pathinfo($file['name'], PATHINFO_EXTENSION) == 'zip'){
			$fileContents = base64_encode("Zip file contents is meaningless");
		}

		# return an array of the contents and name
		return ["contents" => $fileContents, "name" => $fileName];
	}
	
	/* 
		This function will take a user and problem, generate the 
		appropriate folders for it, and return the directory
	*/
	public function getUploadDirectory($user, $problem){
		
		$target_directory = $this->web_directory."compilation/uploads/".$user->id."/".$problem->id."/";
		
		# clear out the uploads directory and rebuild it
		shell_exec("rm -rf ".$target_directory);
		shell_exec("mkdir -p ".$target_directory);
		
		return $target_directory;
	}

	/*
		This function will take a file, a user, and a problem and
		put the file in the appropriate directory by using the 
		getUploadDirectory method
	*/		
	public function uploadSubmissionFile($file, $user, $problem){
		
		# file paths
		$target_directory = $this->getUploadDirectory($user, $problem);
		$target_file = $target_directory.$file->getClientOriginalName();		
		
		$moved_file = $file->move($target_directory, $file->getClientOriginalName());
		
		# check to see if the file was uploaded
		if($moved_file){
			return $target_file;
			
		} else {
			return null;		
		}		
	}	
	
}










?>