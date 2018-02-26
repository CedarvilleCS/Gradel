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
use AppBundle\Utils\Generator;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class UploadController extends Controller {

	/*
		Saves a PHP array of the file contents using the Uploader utility in $data
		Returns 1 on success
	*/
	public function getContentsAction(Request $request){

		if(!isset($_FILES['file']['error']) || is_array($_FILES['file']['error'])) {
			return $this->returnForbiddenResponse('File must be smaller than 1Mb.');
		}
		
		switch ($_FILES['file']['error']) {
			case UPLOAD_ERR_OK:
				break;
			case UPLOAD_ERR_NO_FILE:
				return $this->returnForbiddenResponse('No file sent.');
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				return $this->returnForbiddenResponse('File must be smaller than 1Mb.');
			default:
				return $this->returnForbiddenResponse('Unknown errors.');
		}
		
		$file = $_FILES["file"];
		
		if($file['size'] > 1024*1024){
			return $this->returnForbiddenResponse('File must be smaller than 1Mb.');
		}
		
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
	private function aceUpload(&$data, $problem_id, $postData){

		# entity manager
        $em = $this->getDoctrine()->getManager();
		$web_dir = $this->get('kernel')->getProjectDir()."/";
		
		$generator = new Generator($em, $web_dir);
				
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

		# get filename and information
		$filename = null;
		$main_class = null;
		$package_name = null;
		$language = null;
		
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

		$data = [
			'problem_id' => $problem_entity->id,
			'submitted_filename' => $filename,
			'language_id' => $language->id,
			'main_class' => $main_class,
			'package_name' => $package_name
		];
		
		return 1;
	}

	/*
		Handles uploading code from the file input/selector on the assignment page
		It is called from the submitProblemUploadAction method
	*/
	private function fileUpload(&$data, $problem_id, $postData, $file){

		# entity manager
        $em = $this->getDoctrine()->getManager();
		$web_dir = $this->get('kernel')->getProjectDir()."/";
		
		$generator = new Generator($em, $web_dir);

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

			# get filename and information
			$filename = null;
			$main_class = null;
			$package_name = null;
			$language = null;
			
			$response = $generator->generateFilename($filename, $language, $main_class, $package_name, $problem_entity, $postData);
			
			if($response != 1){
				return $response;
			}	

			$data = [
				'problem_id' => $problem_entity->id,
				'submitted_filename' => basename($file->getClientOriginalName()),
				'language_id' => $language->id,
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

			if($file->getClientSize() > 1024*1024){
				return $this->returnForbiddenResponse("FILE GIVEN IS TOO LARGE");
			}
			else if($file->getClientSize() <= 0){
				return $this->returnForbiddenResponse("FILE GIVEN IS EMPTY");
			}		
			
			$data = null;
			$uploadResp = $this->fileUpload($data, $problem_id, $postData, $file);
			
			if($uploadResp != 1){
				return $this->returnForbiddenResponse($uploadResp."");
			}
			
		} else if(isset($postData["ACE"]) && trim($postData["ACE"]) != ""){
			
			if(strlen($postData["ACE"]) > 1024*1024){
				return $this->returnForbiddenResponse("UPLOADED CODE IS TOO LONG");
			}
			else if(strlen($postData["ACE"]) <= 0){
				return $this->returnForbiddenResponse("UPLOADED CODE IS EMPTY");
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
