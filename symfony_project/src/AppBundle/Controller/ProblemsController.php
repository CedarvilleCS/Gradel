<?php

namespace AppBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use AppBundle\Entity\Submission;
use AppBundle\Entity\Problem;
use AppBundle\Entity\ProblemLanguage;

use Psr\Log\LoggerInterface;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\Response;


class ProblemsController extends Controller {

    public function problemsAction($userId, $sectionId, $assignmentId, $problemId) {

	
	#path: '/home/{userId}/section/{sectionId}/assignment/{assignmentId}/problem/{problemId}'
	
		$em = $this->getDoctrine()->getManager();
		
		$assignment_entity = $em->find("AppBundle\Entity\Assignment", $assignmentId);

		if(!assignment_entity){
			die("ASSIGNMENT DOES NOT EXIST");
		}
		
		if($problemId == 0){
			$problemId = $assignment_entity->problems[0]->id;
		}
		
		$problem_entity = $em->find("AppBundle\Entity\Problem", $problemId);

		if(!problem_entity){
			die("PROBLEM DOES NOT EXIST");
		}

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

		return $this->render('problems/index.html.twig', [
			'problem' => $problem_entity,
			'problemDescription' => $currentProblemDescription,
			'languages' => $languages,
			'user_subs' => $user_subs
		]);
    }

    public function newAction($userId, $sectionId, $assignmentId) {
      return $this->render('problems/new.html.twig', [
        'userId' => $userId,
        'sectionId' => $sectionId,
        'assignmentId' => $assignmentId,
      ]);
    }

    public function editAction() {
      return $this->render('problems/edit.html.twig', [

      ]);
    }
}

?>
