<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Entity\Course;
use AppBundle\Entity\UserSectionRole;
use AppBundle\Entity\Section;
use AppBundle\Entity\Assignment;

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
			
			# get the user submissions for each problem
			$qb_subs = $em->createQueryBuilder();
			$qb_subs->select('s')
				->from('AppBundle\Entity\Submission', 's')
				->where('s.team = ?1')
				->andWhere('s.problem IN (?2)')
				->andWhere('s.is_accepted = true')
				->setParameter(1, 3)
				->setParameter(2, array(1,2,3,4,5,6));
				
			$sub_query = $qb_subs->getQuery();
			$subs = $sub_query->getResult();
			
			$user_subs = [];
			foreach($subs as $submission){
				$user_subs[$submission->problem->id] = $submission->percentage;
			}
			
			//echo json_encode($user_subs);
			//die();
			
			$currentProblemDescription = stream_get_contents($problem_entity->description);
			$problem_languages = $problem_entity->problem_languages;

			$languages = [];
			foreach($problem_languages as $pl){
				$languages[] = $pl->language;
			}
		}
			
		return $this->render('assignment/index.html.twig', [
			'user' => $user,
			'section' => $assignment_entity->section,
			'assignment' => $assignment_entity,
			'problem' => $problem_entity,
			
			'problemDescription' => $currentProblemDescription,
			'languages' => $languages,
			'user_subs' => $user_subs,
			'usersectionrole' => $usersectionrole
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

      $gradingmethod = $em->find('AppBundle\Entity\Gradingmethod', 1);

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
}

?>
