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
use AppBundle\Entity\Gradingmethod;
use AppBundle\Entity\Filetype;
use AppBundle\Entity\Feedback;
use AppBundle\Entity\TestcaseResult;

use Psr\Log\LoggerInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class UploadController extends Controller {
 
    public function uploadAction($assignment_id, $problem_id) {
		
        # entity manager
        $em = $this->getDoctrine()->getManager();
        
        # get the current problem
        $problem_entity = $em->find("AppBundle\Entity\Problem", $problem_id);        
		if(!$problem_entity){
            die("PROBLEM DOES NOT EXIST");
        } else{
            echo($problem_entity->id."<br/>");    
        }        
        
        # get the current user
        $user_entity= $this->get('security.token_storage')->getToken()->getUser();        
        if(!$user_entity){
            die("USER DOES NOT EXIST");
        } else{
            echo($user_entity->getFirstName()." ".$user_entity->getLastName()."<br/>");
        }
		
        // web_dir is /var/www/gradel_dev/user/gradel/symfony_project		
        // save uploaded file to $web_dir.compilation/uploads/user_id/
        $web_dir = $this->get('kernel')->getProjectDir()."/";
		$uploads_directory = $web_dir."compilation/uploads/".$user_entity->id."/".$problem_entity->id."/";
		
		# clear out the uploads directory and rebuild it
		shell_exec("rm -rf ".$uploads_directory);		
		shell_exec("mkdir -p ".$uploads_directory);
		
        $target_file = $uploads_directory . basename($_FILES["fileToUpload"]["name"]);

        // Check if file already exists       
		if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
			echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
						
			return $this->redirectToRoute('submit', 
										array('problem_id' => $problem_entity->id, 
												'submitted_filename' => basename($_FILES["fileToUpload"]["name"]),
												'language_id' => 4,
												'main_class' => 'Sum',
												'package_name' => ''));
												
												
			// INDICATE THAT FILE UPLOAD WAS SUCCESSFUL ON ASSIGNMENT/PROBLEM PAGE
		} else {
			echo "Sorry, there was an error uploading your file.";
		}
		
        // if they didn't send a file, render upload page
        return new Response();
    }
}

?>
