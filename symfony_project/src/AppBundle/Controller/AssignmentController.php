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

		// If a file has been uploaded
		if (file_get_contents($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
			$fileContents = file_get_contents($_FILES["fileToUpload"]["tmp_name"], $target_file);
			

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
				"fileContents" => base64_encode($fileContents),
			]);
		}

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
			"fileContents" => $fileContents,
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

	public function myuploadAction($sectionId, $assignmentId, $problemId) {
		// echo("myuploadAction");
		if (file_get_contents($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
			$fileContents = file_get_contents($_FILES["fileToUpload"]["tmp_name"], $target_file);
			// die($fileContents);
			// echo('<script>
			// 	var editor = ace.edit("editor");
			// 	editor.setValue("test");
			// </script>');

			// die($fileContents);
		}


		// die();
		return $this->redirectToRoute('assignment', 
				['sectionId' => $sectionId,
				'assignmentId' => $problem_entity->assignment->id,
				'problemId' => $problem_entity->id]);
	}
}

?>
