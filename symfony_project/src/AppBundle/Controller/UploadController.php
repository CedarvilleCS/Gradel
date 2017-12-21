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
use AppBundle\Entity\AssignmentGradingMethod;
use AppBundle\Entity\Feedback;
use AppBundle\Entity\TestcaseResult;

use Psr\Log\LoggerInterface;

use AppBundle\Utils\Uploader;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class UploadController extends Controller {

	/*
		Returns a json array of the file contents using the Uploader utility
	*/
	public function getContentsAction(Request $request){

		if(!$_FILES["file"]){
			$response = new Response("You should have provided a file, silly!");
			$response->setStatusCode(Response::HTTP_FORBIDDEN);
			return $response;
		}

		$web_dir = $this->get('kernel')->getProjectDir()."/";
        $uploader = new Uploader($web_dir);

		$fileInfo = $uploader->getFileContents($_FILES["file"]);

		$response = new Response(json_encode([

			'contents' => $fileInfo['contents'],
			'file' => $fileInfo['name'],

		]));

		$response->headers->set('Content-Type', 'application/json');
		$response->setStatusCode(Response::HTTP_OK);

		return $response;
	}

	/*
		Handles uploading code from the ACE editor on the assignment page
		It is called from the submitProblemUploadAction method
	*/
	public function aceUpload($problem_id){

		# entity manager
        $em = $this->getDoctrine()->getManager();
		
        # get the current problem
        $problem_entity = $em->find("AppBundle\Entity\Problem", $problem_id);
		if(!$problem_entity){
			
            die("PROBLEM DOES NOT EXIST");
        }

        # get the current user
        $user= $this->get('security.token_storage')->getToken()->getUser();
        if(!$user){
            die("USER DOES NOT EXIST");
        }

		# check the language
		if($_POST["language"]){
			$language_id = $_POST["language"];
		} else {
			$language_id = $_POST["languageId"];
		}
		
		$language_entity = $em->find("AppBundle\Entity\Language", $language_id);
		if(!$language_entity){
			die("LANGUAGE DOES NOT EXIST!");
		}
		if($language_entity->name == "Java"){

			if((!$_POST["main_class"] || $_POST["main_class"] == "") && (!$_POST["mainclass"] || $_POST["mainclass"] == "")){
				die("MAIN CLASS IS NEEDED");
			}
			
			$main_class = null;
			if(!$_POST["main_class"]){
				$main_class = $_POST["mainclass"];
				$package_name = $_POST["packagename"];
			} else {
				$main_class = $_POST["main_class"];
				$package_name = $_POST["package_name"];
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

		if(!file_put_contents($uploads_directory . $filename, $_POST["ACE"], FILE_USE_INCLUDE_PATH)){
			die("UNABLE TO MOVE THE ACE EDITOR CONTENTS");
		}

		return [
			'problem_id' => $problem_entity->id,
			'submitted_filename' => $filename,
			'language_id' => $language_id,
			'main_class' => $main_class,
			'package_name' => $package_name
		];
	}

	/*
		Handles uploading code from the file input/selector on the assignment page
		It is called from the submitProblemUploadAction method
	*/
	public function fileUpload($problem_id, $postData, $file){

		# entity manager
        $em = $this->getDoctrine()->getManager();

        # get the current problem
        $problem_entity = $em->find("AppBundle\Entity\Problem", $problem_id);
		if(!$problem_entity){
            die("PROBLEM DOES NOT EXIST");
        }

        # get the current user
        $user= $this->get('security.token_storage')->getToken()->getUser();
        if(!$user){
            die("USER DOES NOT EXIST");
        }

        

		# save uploaded file to $web_dir.compilation/uploads/user_id/
        $web_dir = $this->get('kernel')->getProjectDir()."/";

        $uploader = new Uploader($web_dir);
		$target_file = $uploader->uploadSubmissionFile($file, $user, $problem_entity);
		
		if($target_file){

			$language_id = $postData["language"];

			$language_entity = $em->find("AppBundle\Entity\Language", $language_id);
			if(!$language_entity){
				die("LANGUAGE DOES NOT EXIST!");
			}

			if($language_entity->name == "Java"){

				if(strlen($postData["main_class"]) == 0){
					die("MAIN CLASS IS NEEDED");
				}

				$main_class = $postData["main_class"];
				$package_name = $postData["package_name"];

			} else {
				$main_class = "";
				$package_name = "";
			}

			return [
				'problem_id' => $problem_entity->id,
				'submitted_filename' => basename($file->getClientOriginalName()),
				'language_id' => $language_id,
				'main_class' => $main_class,
				'package_name' => $package_name,
			];
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
			
			$data = $this->fileUpload($problem_id, $postData, $file);
			
		} else if(isset($postData["ACE"]) && trim($postData["ACE"]) != ""){
			
			if(strlen($_POST["ACE"]) > 1048576){
				return $this->returnForbiddenResponse("UPLOADED CODE IS TOO LONG");
			}	
			
			$data = $this->aceUpload($problem_id, $postData);

		} else {

			# get the current problem
			$em = $this->getDoctrine()->getManager();
			$problem_entity = $em->find("AppBundle\Entity\Problem", $problem_id);
			if(!$problem_entity){
				return $this->returnForbiddenResponse("PROBLEM PROVIDED DOES NOT EXIST!");
			}
			
			
			return $this->returnForbiddenResponse("NOTHING PROVIDED TO UPLOAD");
		}


		$response = new Response(json_encode([
			'redirect_url' => $url,
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
