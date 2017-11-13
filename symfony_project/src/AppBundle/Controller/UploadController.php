<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\Submission;

use AppBundle\Utils\Grader;

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
use AppBundle\Entity\ProblemGradingMethod;
use AppBundle\Entity\AssignmentGradingMethod;
use AppBundle\Entity\Feedback;
use AppBundle\Entity\TestcaseResult;

use Psr\Log\LoggerInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class UploadController extends Controller {
 
    public function uploadAction($problem_id) {

		$fileContents = "";
		
		# echo(var_dump($_POST));
		# echo(var_dump($_FILES));
		# die();
		
        # entity manager
        $em = $this->getDoctrine()->getManager();
        
        # get the current problem
        $problem_entity = $em->find("AppBundle\Entity\Problem", $problem_id);        
		if(!$problem_entity){
            die("PROBLEM DOES NOT EXIST");
        } else{
            #echo($problem_entity->id."<br/>");    
        }        
        
        # get the current user
        $user= $this->get('security.token_storage')->getToken()->getUser();        
        if(!$user){
            die("USER DOES NOT EXIST");
        } else{
            #echo($user->getFirstName()." ".$user->getLastName()."<br/>");
        }
		
        // web_dir is /var/www/gradel_dev/user/gradel/symfony_project		
        // save uploaded file to $web_dir.compilation/uploads/user_id/
        $web_dir = $this->get('kernel')->getProjectDir()."/";
		$uploads_directory = $web_dir."compilation/uploads/".$user->id."/".$problem_entity->id."/";
		
		# clear out the uploads directory and rebuild it
		shell_exec("rm -rf ".$uploads_directory);		
		shell_exec("mkdir -p ".$uploads_directory);
		
        $target_file = $uploads_directory . basename($_FILES["fileToUpload"]["name"]);									
												
		// INDICATE THAT FILE UPLOAD WAS SUCCESSFUL ON ASSIGNMENT/PROBLEM PAGE
		
			
		if($_POST["ACE"] != "") { // If ACE is not blank, and no file was uploaded, create a file with the ACE contents

			#echo "Sorry, there was an error uploading your file.";
			$language_id = $_POST["language"];
			
			$language_entity = $em->find("AppBundle\Entity\Language", $language_id);			
			if(!$language_entity){
				die("LANGUAGE DOES NOT EXIST!");
			}

			if($language_entity->name == "Java"){
				
				if(strlen($_POST["main_class"]) == 0){
					die("MAIN CLASS IS NEEDED");
				}
				
				$main_class = $_POST["main_class"];
				$package_name = $_POST["package_name"];

				file_put_contents($uploads_directory . $main_class . $language_entity->filetype, $_POST["ACE"], FILE_USE_INCLUDE_PATH);
				
				$submitted_filename = $main_class . $language_entity->filetype;

			} else {
				$main_class = '';
				$package_name = '';

				file_put_contents($uploads_directory . "problem". $problem_entity->id . $language_entity->filetype, $_POST["ACE"], FILE_USE_INCLUDE_PATH);

				$submitted_filename = "problem". $problem_entity->id . $language_entity->filetype;
			}
			
			return $this->redirectToRoute('submit', 
										array('problem_id' => $problem_entity->id, 
												'submitted_filename' => $submitted_filename,
												'language_id' => $language_id,
												'main_class' => $main_class,
												'package_name' => $package_name));
		}
		
        // if they didn't send a file, render upload page
		return $this->redirectToRoute('assignment', 
									array('sectionId' => $problem_entity->assignment->section->id,
											'assignmentId' => $problem_entity->assignment->id,
											'problemId' => $problem_entity->id));
    }
}

?>
