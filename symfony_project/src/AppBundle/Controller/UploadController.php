<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\Submission;

use AppBundle\Entity\Role;
use AppBundle\Entity\User;
use AppBundle\Entity\Team;
use AppBundle\Entity\Course;
use AppBundle\Entity\Section;
use AppBundle\Entity\Assignment;
use AppBundle\Entity\Problem;
use AppBundle\Entity\UserSectionRole;
use AppBundle\Entity\Testcase;
use AppBundle\Entity\Language;
use AppBundle\Entity\Feedback;
use AppBundle\Entity\TestcaseResult;

use Psr\Log\LoggerInterface;

use AppBundle\Utils\Uploader;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class UploadController extends Controller {

	/*
		Saves a PHP array of the file contents using the Uploader utility in $data
		Returns 1 on success
	*/
	public function getContentsAction(Request $request){
	
		if(!$_FILES["file"]){
			return $this->returnForbiddenResponse("A file from the input with name='file' was not provided.");
		}
		
		$file = $_FILES["file"];
		
		$web_dir = $this->get('kernel')->getProjectDir()."/";
        $uploader = new Uploader($web_dir);

		$fileInfo = $uploader->getFileContents($file);

		$responseData = [];		
		$responseData[] = $fileInfo;
		
		$response = new Response(json_encode([
			
			'files' => $responseData,

		]));

		$response->headers->set('Content-Type', 'application/json');
		$response->setStatusCode(Response::HTTP_OK);

		return $response;
	}

	/*
		Handles uploading code from the ACE editor on the assignment page
		It is called from the submitProblemUploadAction method
	*/
	public function aceUpload(&$data, $problem_id, $postData){

		# entity manager
        $em = $this->getDoctrine()->getManager();
				
        # get the current problem
		
		if(!isset($problem_id) || !($problem_id > 0)){
			return "PROBLEM ID WAS NOT PROVIDED OR FORMATTED PROPERLY";
		}
		
        $problem_entity = $em->find("AppBundle\Entity\Problem", $problem_id);
		if(!$problem_entity){
			
            return "PROBLEM DOES NOT EXIST";
        }

        # get the current user
        $user= $this->get('security.token_storage')->getToken()->getUser();
        if(!$user){
             return "USER DOES NOT EXIST";
        }

		# check the language
		if($postData["language"]){
			$language_id = $postData["language"];
		} else {
			$language_id = $postData["languageId"];
		}
		
		if(!isset($language_id) || !($language_id > 0)){
			return "LANGUAGE WAS NOT PROVIDED OR FORMATTED PROPERLY";
		}
		
		$language_entity = $em->find("AppBundle\Entity\Language", $language_id);
		if(!$language_entity){
			 return "LANGUAGE DOES NOT EXIST!";
		}
		if($language_entity->name == "Java"){

			if((!isset($postData["main_class"]) || trim($postData["main_class"]) == "") && (!isset($postData["mainclass"]) || trim($postData["mainclass"]) == "")){
				 return "MAIN CLASS IS NEEDED";
			}
			
			$main_class = null;
			if(!$postData["main_class"]){
				$main_class = $postData["mainclass"];
				$package_name = $postData["packagename"];
			} else {
				$main_class = $postData["main_class"];
				$package_name = $postData["package_name"];
			}

			$main_class = $main_class;
			$package_name = $package_name;

			$filename = $main_class.".java";

		} else {
			$main_class = "";
			$package_name = "";

			$filename = "problem". $problem_entity->id . $language_entity->filetype;
		}
		
		# save uploaded file to $web_dir.compilation/uploads/user_id/problem
        $web_dir = $this->get('kernel')->getProjectDir()."/";
        $uploader = new Uploader($web_dir);


		$uploads_directory = $uploader->getUploadDirectory($user, $problem_entity);

		if(!file_put_contents($uploads_directory . $filename, $postData["ACE"], FILE_USE_INCLUDE_PATH)){
			 return "UNABLE TO MOVE THE ACE EDITOR CONTENTS";
		}

		$data = [
			'problem_id' => $problem_entity->id,
			'submitted_filename' => $filename,
			'language_id' => $language_id,
			'main_class' => $main_class,
			'package_name' => $package_name
		];
		
		return 1;
	}

	/*
		Handles uploading code from the file input/selector on the assignment page
		It is called from the submitProblemUploadAction method
	*/
	public function fileUpload(&$data, $problem_id, $postData, $file){

		# entity manager
        $em = $this->getDoctrine()->getManager();

        # get the current problem
		if(!isset($problem_id) || !($problem_id > 0)){
			return "PROBLEM ID WAS NOT PROVIDED OR FORMATTED PROPERLY";
		}
		
        $problem_entity = $em->find("AppBundle\Entity\Problem", $problem_id);
		if(!$problem_entity){
            return "PROBLEM DOES NOT EXIST";
        }

        # get the current user
        $user= $this->get('security.token_storage')->getToken()->getUser();
        if(!$user){
           return "USER DOES NOT EXIST";
        }

        

		# save uploaded file to $web_dir.compilation/uploads/user_id/
        $web_dir = $this->get('kernel')->getProjectDir()."/";

        $uploader = new Uploader($web_dir);
		$target_file = $uploader->uploadSubmissionFile($file, $user, $problem_entity);
		
		if($target_file){

			$language_id = $postData["language"];
			
			if(!isset($language_id) || !($language_id > 0)){
				return "LANGUAGE WAS NOT PROVIDED OR FORMATTED PROPERLY";
			}			

			$language_entity = $em->find("AppBundle\Entity\Language", $language_id);
			if(!$language_entity){
				return "LANGUAGE DOES NOT EXIST!";
			}

			if($language_entity->name == "Java"){

				if(strlen($postData["main_class"]) == 0){
					return "MAIN CLASS IS NEEDED";
				}

				$main_class = $postData["main_class"];
				$package_name = $postData["package_name"];

			} else {
				$main_class = "";
				$package_name = "";
			}

			$data = [
				'problem_id' => $problem_entity->id,
				'submitted_filename' => basename($file->getClientOriginalName()),
				'language_id' => $language_id,
				'main_class' => $main_class,
				'package_name' => $package_name,
			];
			
			return 1;
		}
	}

	/*
		Controller action to handle the submission of code on the assignment page
	*/
    public function submitProblemUploadAction($problem_id, Request $request) {		

		$postData = $request->request->all();
		$files = $request->files;		
		
		if($files->get('file')){	

			$file = $files->get('file');

			if($file->getClientSize() > 1048576){
				return $this->returnForbiddenResponse("FILE GIVEN IS TOO LARGE");
			}			
			
			$data = null;
			$uploadResp = $this->fileUpload($data, $problem_id, $postData, $file);
			
			if($uploadResp != 1){
				return $this->returnForbiddenResponse($uploadResp."");
			}
			
		} else if(isset($postData["ACE"]) && trim($postData["ACE"]) != ""){
			
			if(strlen($postData["ACE"]) > 1048576){
				return $this->returnForbiddenResponse("UPLOADED CODE IS TOO LONG");
			}	
			
			$data = null;
			$uploadResp = $this->aceUpload($data, $problem_id, $postData);
			
			if($uploadResp != 1){
				return $this->returnForbiddenResponse($uploadResp."");
			}

		} else {
			
			return $this->returnForbiddenResponse("NOTHING PROVIDED TO UPLOAD");
		}

		$response = new Response(json_encode([
			'data' => $data,
		]));

		$response->headers->set('Content-Type', 'application/json');
		$response->setStatusCode(Response::HTTP_OK);

		return $response;
    }
	
	private function returnForbiddenResponse($message){		
		$response = new Response($message);
		$response->setStatusCode(Response::HTTP_FORBIDDEN);
		return $response;
	}
	
}

?>
