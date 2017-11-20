<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Entity\Course;
use AppBundle\Entity\UserSectionRole;
use AppBundle\Entity\Section;
use AppBundle\Entity\Assignment;

use AppBundle\Utils\Grader;
use AppBundle\Utils\Uploader;

use \DateTime;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Response;
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
			
			if(!$problem_entity || $problem_entity->assignment != $assignment_entity){
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
		
		# figure out how many attempts they have left
		$total_attempts = $problem_entity->gradingmethod->total_attempts;
		if($total_attempts == 0){
			$attempts_remaining = -1;
		} else {
			$attempts_remaining = max($total_attempts - $grader->getNumTotalAttempts($user, $problem_entity), 0);
		}
		
		
		# get the get the best submission so far
		$qb_accsub = $em->createQueryBuilder();
		$qb_accsub->select('s')
			->from('AppBundle\Entity\Submission', 's')
			->where('s.team = ?1')
			->andWhere('s.problem = ?2')
			->andWhere('s.is_accepted = true')
			->setParameter(1, $grader->getTeam($user, $assignment_entity))
			->setParameter(2, $problem_entity);
			
		$sub_query = $qb_accsub->getQuery();
		$best_submission = $sub_query->getOneOrNullResult();

		
		return $this->render('assignment/index.html.twig', [
			'user' => $user,
			'section' => $assignment_entity->section,
			'assignment' => $assignment_entity,
			'problem' => $problem_entity,

			'problemDescription' => $currentProblemDescription,
			'languages' => $languages,
			'usersectionrole' => $usersectionrole,
			'grader' => new Grader($em),
			
			'attempts_remaining' => $attempts_remaining,
			'best_submission' => $best_submission,

			'default_code' => $default_code,
			'ace_modes' => $ace_modes,
			'filetypes' => $filetypes,
		]);	
	}

    public function editAction($sectionId, $assignmentId) {

	$em = $this->getDoctrine()->getManager();

	if($assignmentId != 0){
		$assignment = $em->find('AppBundle\Entity\Assignment', $assignmentId);
	}

	return $this->render('assignment/edit.html.twig', [
		"assignment" => $assignment,
		"edit" => true,
		]);
    }

    public function deleteAction($sectionId, $assignmentId){
	
		$em = $this->getDoctrine()->getManager();

		$assignment = $em->find('AppBundle\Entity\Assignment', $assignmentId);	  
		if(!$assignment){
			die("ASSIGNMENT DOES NOT EXIST");
		}
		
		$user = $this->get('security.token_storage')->getToken()->getUser();
		if(!$user){
			die("USER DOES NOT EXIST");
		}
		
		# validate the user
		$grader = new Grader($em);
		if(!$user->hasRole("ROLE_SUPER") && !$user->hasRole("ROLE_ADMIN") && !$grader->isTeaching($user, $assignment->section)){
			die("YOU ARE NOT ALLOWED TO DELETE THIS ASSIGNMENT");			
		}
		
		$em->remove($assignment);
		$em->flush();
		return $this->redirectToRoute('section', ['sectionId' => $assignment->section->id]);
	}
	
	
    public function modifyPostAction(Request $request) {
		
		return $this->returnForbiddenResponse("UHOH!");
		
		$url = "HELLO!";
		
		$response = new Response(json_encode(array('redirect_url' => $url)));
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
