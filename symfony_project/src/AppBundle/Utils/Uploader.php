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
use AppBundle\Entity\Trial;
use AppBundle\Entity\Language;
use AppBundle\Entity\Feedback;
use AppBundle\Entity\TestcaseResult;


use AppBundle\Utils\Zipper;


use \DateTime;
use \ZipArchive;

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

		
				
		if(pathinfo($file['name'], PATHINFO_EXTENSION) == 'zip'){
			
			# ZIP
			$zipper = new Zipper();
			$contents = $zipper->getZipContents($file["tmp_name"]);
			
			if($contents === false){
				return false;
			}
			
			return $contents;				
			
		} else {
			
			# Get the file contents and name
			$fileContents = base64_encode(file_get_contents($file["tmp_name"]));
			$fileName = basename($file["name"]);

			# return an array of the contents and name
			return [["contents" => $fileContents, "name" => $fileName]];		
		}
	}
	
	/* 
		This function will take a user and problem, generate the 
		appropriate folders for it, and return the directory
	*/
	public function createUploadDirectory($user, $problem){
		
		$target_directory = $this->web_directory."compilation/uploads/".$user->id."/".$problem->id."/";
		
		# clear out the uploads directory and rebuild it
		shell_exec("rm -rf ".$target_directory);
		shell_exec("mkdir -p ".$target_directory);
		
		return $target_directory;
	}

	/*
		This function will take a file, a user, and a problem and
		put the file in the appropriate directory by using the 
		createUploadDirectory method
	*/		
	public function uploadSubmissionFile($file, $user, $problem){
		
		# file paths
		$target_directory = $this->createUploadDirectory($user, $problem);
		$target_file = $target_directory.$file->getClientOriginalName();		
		
		$moved_file = $file->move($target_directory, $file->getClientOriginalName());
		
		# check to see if the file was uploaded
		if($moved_file){
			return $target_file;
			
		} else {
			return null;		
		}		
	}	
	
	public function createFile($text, $user, $problem, $filename){
		
		$response = $generator->generateFilename($filename, $language, $main_class, $package_name, $problem_entity, $postData);
		
		if($response != 1){
			return $response;
		}		
		
		# save uploaded file to $web_dir.compilation/uploads/user_id/problem
        $web_dir = $this->get('kernel')->getProjectDir()."/";
        $uploader = new Uploader($web_dir);


		$uploads_directory = $uploader->createUploadDirectory($user, $problem_entity);

		if(!file_put_contents($uploads_directory . $filename, $postData["ACE"], FILE_USE_INCLUDE_PATH)){
			 return "UNABLE TO MOVE THE ACE EDITOR CONTENTS";
		}
		
		
	}
	
	
	/*
		This function will take a trial and 
		put the contents of the trial in a file and return the location
		of the new file.
	
	*/
	public function createSubmissionFile($trial){
		
		# file paths
		$target_directory = $this->createUploadDirectory($trial->user, $trial->problem);
		$target_file = $target_directory.$trial->filename;		
		
		$fp = fopen($target_file, 'w');
		
		# check to see if the file was uploaded
		if(fwrite($fp, $trial->deblobinateFile())){
			return $trial->filename;
		} else {
			return null;
		}
	}	


	/*
		This function will take a user, problem, filename, and file and 
		put the contents in the upload directory for compiling
	
	*/
	public function createGeneratorFile($user, $problem, $filename, $file){
		
		$target_directory = $this->createUploadDirectory($user, $problem);
		$target_file = $target_directory.$filename;

		$fp = fopen($target_file, 'w');

		rewind($file);
		$val = stream_get_contents($file);
		rewind($file);
		
		# if the file is actually just text
		if($val == false){			
			return $file;
		} 
		# if the file writing worked
		else if(fwrite($fp, $val)){
			chmod($target_file, 0777);
			return $filename;
		}
		# error 
		else {
			return null;
		}		
	}
	
}










?>