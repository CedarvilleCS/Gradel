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

	# returns a json array of the file contents
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

        // web_dir is /var/www/gradel_dev/user/gradel/symfony_project
        // save uploaded file to $web_dir.compilation/uploads/user_id/
        $web_dir = $this->get('kernel')->getProjectDir()."/";

        $uploader = new Uploader($web_dir);

		$language_id = $_POST["language"];

		$language_entity = $em->find("AppBundle\Entity\Language", $language_id);
		if(!$language_entity){
			die("LANGUAGE DOES NOT EXIST!");
		}

		$uploads_directory = $uploader->getUploadDirectory($user, $problem_entity);

		if($language_entity->name == "Java"){

			if(!$_POST["main_class"] || $_POST["main_class"] == ""){
				die("MAIN CLASS IS NEEDED");
			}

			$main_class = $_POST["main_class"];
			$package_name = $_POST["package_name"];

			$filename = $main_class.".java";

		} else {
			$main_class = '';
			$package_name = '';

			$filename = "problem". $problem_entity->id . $language_entity->filetype;
		}

		if(!file_put_contents($uploads_directory . $filename, $_POST["ACE"], FILE_USE_INCLUDE_PATH)){
			die("UNABLE TO MOVE THE ACE EDITOR CONTENTS");
		}

		return $this->generateUrl('submit', [

			'problem_id' => $problem_entity->id,
			'submitted_filename' => $filename,
			'language_id' => $language_id,
			'main_class' => $main_class,
			'package_name' => $package_name

		]);
	}

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

        // web_dir is /var/www/gradel_dev/user/gradel/symfony_project
        // save uploaded file to $web_dir.compilation/uploads/user_id/
        $web_dir = $this->get('kernel')->getProjectDir()."/";

        $uploader = new Uploader($web_dir);
		$target_file = $uploader->uploadSubmissionFile($file, $user, $problem_entity);

		#echo $target_file;
		#die();
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
				$main_class = '';
				$package_name = '';
			}

			return $this->generateUrl('submit', [

				'problem_id' => $problem_entity->id,
				'submitted_filename' => basename($file["name"]),
				'language_id' => $language_id,
				'main_class' => $main_class,
				'package_name' => $package_name,

			]);
		}
	}

    public function submitProblemUploadAction($problem_id) {

		if($_FILES["file"]){

			$url = $this->fileUpload($problem_id, $_POST, $_FILES["file"]);

		} else if($_POST["ACE"] && $_POST["ACE"] != ""){

			$url = $this->aceUpload($problem_id, $_POST);

		} else {

			# get the current problem
			$em = $this->getDoctrine()->getManager();
			$problem_entity = $em->find("AppBundle\Entity\Problem", $problem_id);
			if(!$problem_entity){
				die("PROBLEM DOES NOT EXIST");
			}
			// if they didn't send a file or ace, render upload page
			$url = $this->generateUrl('assignment', [

				'sectionId' => $problem_entity->assignment->section->id,
				'assignmentId' => $problem_entity->assignment->id,
				'problemId' => $problem_entity->id,

			]);
		}


		$response = new Response(json_encode([

			'redirect_url' => $url,

		]));

		$response->headers->set('Content-Type', 'application/json');
		$response->setStatusCode(Response::HTTP_OK);

		return $response;
    }
}

?>
