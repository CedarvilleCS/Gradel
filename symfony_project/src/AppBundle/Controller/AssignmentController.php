<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Entity\Course;
use AppBundle\Entity\UserSectionRole;
use AppBundle\Entity\Section;
use AppBundle\Entity\Assignment;

use AppBundle\Utils\Grader;

use \DateTime;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Psr\Log\LoggerInterface;

class AssignmentController extends Controller {


	public function assignmentAction($sectionId, $assignmentId, $problemId) {

		$em = $this->getDoctrine()->getManager();
		
		$user = $this->get('security.token_storage')->getToken()->getUser();  	  
		if(!get_class($user)){
			die("USER DOES NOT EXIST!");		  
		}
		
		$assignment_entity = $em->find("AppBundle\Entity\Assignment", $assignmentId);
		if(!assignment_entity){
			die("ASSIGNMENT DOES NOT EXIST");
		}
		
		if($problemId == 0){
			$problemId = $assignment_entity->problems[0]->id;
		}
		
		if($problemId != null){
		
			$problem_entity = $em->find("AppBundle\Entity\Problem", $problemId);
			
			if(!problem_entity){
				die("PROBLEM DOES NOT EXIST");
			}

			# get the usersectionrole
			$qb_usr = $em->createQueryBuilder();
			$qb_usr->select('usr')
				->from('AppBundle\Entity\UserSectionRole', 'usr')
				->where('usr.user = ?1')
				->andWhere('usr.section = ?2')
				->setParameter(1, $user)
				->setParameter(2, $problem_entity->assignment->section);
				
			$usr_query = $qb_usr->getQuery();
			$usersectionrole = $usr_query->getOneOrNullResult();
			
			$currentProblemDescription = stream_get_contents($problem_entity->description);
			$problem_languages = $problem_entity->problem_languages;

			$languages = [];
			$default_code = [];
			$ace_modes = [];
			$filetypes = [];
			
			foreach($problem_languages as $pl){
				$languages[] = $pl->language;
				
				$ace_modes[$pl->language->name] = $pl->language->ace_mode;
				$filetypes[str_replace(".", "", $pl->language->filetype)] = $pl->language->name;
				
				// either get the default code from the problem or from the overall default
				if($pl->default_code != null){
					$default_code[$pl->language->name] = $pl->deblobinateDefaultCode();
				} else{
					$default_code[$pl->language->name] = $pl->language->deblobinateDefaultCode();
				}
			}
		}
		
		$grader = new Grader($em);
		
		$grades = $grader->getAllProblemGrades($user, $assignment_entity);
		#die();
			
			
		return $this->render('assignment/index.html.twig', [
			'user' => $user,
			'section' => $assignment_entity->section,
			'assignment' => $assignment_entity,
			'problem' => $problem_entity,
			
			'problemDescription' => $currentProblemDescription,
			'languages' => $languages,
			'usersectionrole' => $usersectionrole,
			//'grades' => $grades,
			'grader' => new Grader($em),
			
			'default_code' => $default_code,
			'ace_modes' => $ace_modes,
			'filetypes' => $filetypes,
		]);
    }

    public function newAction($sectionId) {

      return $this->render('assignment/new.html.twig', [
        "sectionId" => $sectionId,
      ]);
    }

    public function insertAction($sectionId, $name, $description) {
      $em = $this->getDoctrine()->getManager();
      $user = $this->get('security.token_storage')->getToken()->getUser();

      $assignment = new Assignment();
      $section = $em->find('AppBundle\Entity\Section', $sectionId);

      $gradingmethod = $em->find('AppBundle\Entity\AssignmentGradingMethod', 1);

      $assignment->name = $name;
      $assignment->description = $description;
      $assignment->section = $section;
      $assignment->start_time = new DateTime("now");
      $assignment->end_time = new DateTime("2050-01-01");
      $assignment->cutoff_time = new DateTime("2050-01-01");
      $assignment->weight = 0;
      $assignment->is_extra_credit = false;
      $assignment->gradingmethod = $gradingmethod;

      $em->persist($assignment);
      $em->flush();

      return new RedirectResponse($this->generateUrl('assignment_edit', array('sectionId' => $sectionId, 'assignmentId' => $assignment->id)));

    }

    public function editAction($sectionId, $assignmentId) {

      $em = $this->getDoctrine()->getManager();

      $assignment = $em->find('AppBundle\Entity\Assignment', $assignmentId);

      return $this->render('assignment/edit.html.twig', [
        "sectionId" => $sectionId,
        "assignmentId" => $assignmentId,
        "assignment" => $assignment,
		"description" => stream_get_contents($assignment->description),
      ]);
    }

    public function editQueryAction($sectionId, $assignmentId, $name, $description) {
      $em = $this->getDoctrine()->getManager();

      $user = $this->get('security.token_storage')->getToken()->getUser();


      $assignment = $em->find('AppBundle\Entity\Assignment', $assignmentId);
      $assignment->name = $name;
      $assignment->description = $description;
      // $assignment->start_time = new DateTime("now");
      // $assignment->end_time = new DateTime("2050-01-01");
      // $assignment->cutoff_time = new DateTime("2050-01-01");
      // $assignment->weight = 0;
      // $assignment->is_extra_credit = false;
      // $assignment->gradingmethod = $gradingmethod;

      $em->persist($assignment);
      $em->flush();

      return new RedirectResponse($this->generateUrl('assignment', array('sectionId' => $sectionId, 'assignmentId' => $assignment->id)));
	}
	


// STUFF THAT WAS PREVIOUSLY IN UPLOADCONTROLLER.PHP

	public function uploadAction($problem_id) {
		
		#echo(var_dump($_POST));
		#echo(var_dump($_FILES));
		#die();
		
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

        // Check if file already exists       
		// if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
		// 	#echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";

		// 	$language_id = $_POST["language"];
			
		// 	$language_entity = $em->find("AppBundle\Entity\Language", $language_id);			
		// 	if(!$language_entity){
		// 		die("LANGUAGE DOES NOT EXIST!");
		// 	}
			
		// 	if($language_entity->name == "Java"){
				
		// 		if(strlen($_POST["main_class"]) == 0){
		// 			die("MAIN CLASS IS NEEDED");
		// 		}
				
		// 		$main_class = $_POST["main_class"];	
		// 		$package_name = $_POST["package_name"];		
				
		// 	} else {
		// 		$main_class = '';
		// 		$package_name = '';
		// 	}
			
		// 	return $this->redirectToRoute('submit', 
		// 								array('problem_id' => $problem_entity->id, 
		// 										'submitted_filename' => basename($_FILES["fileToUpload"]["name"]),
		// 										'language_id' => $language_id,
		// 										'main_class' => $main_class,
		// 										'package_name' => $package_name));
												
												
			// INDICATE THAT FILE UPLOAD WAS SUCCESSFUL ON ASSIGNMENT/PROBLEM PAGE
			
		if (file_get_contents($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
			$fileContents = file_get_contents($_FILES["fileToUpload"]["tmp_name"], $target_file);
			// die($fileContents);
			// echo('<script>
			// 	var editor = ace.edit("editor");
			// 	editor.setValue("test");
			// </script>');

		//die($fileContents);


			return $this->redirectToRoute('assignment', 
						['sectionId' => $problem_entity->assignment->section->id,
						'assignmentId' => $problem_entity->assignment->id,
						'problemId' => $problem_entity->id,
						'fileContents' => $fileContents]);

				
					
		} else if($_POST["ACE"] != "") { // If ACE is not blank, and no file was uploaded, create a file with the ACE contents

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
