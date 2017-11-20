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

	$section = $em->find('AppBundle\Entity\Section', $sectionId);
	
	if(!$section){
		die("SECTION DOES NOT EXIST");
	}
	
	if($assignmentId != 0){
		$assignment = $em->find('AppBundle\Entity\Assignment', $assignmentId);
		
		if(!$assignment || $section != $assignment->section){
			die("Assignment does not exist or does not belong to given section");
		}
	}

	return $this->render('assignment/edit.html.twig', [
		"assignment" => $assignment,
		"section" => $section,
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
		
		$em = $this->getDoctrine()->getManager();
		
		# validate the current user
		$user = $this->get('security.token_storage')->getToken()->getUser();
		if(!$user){			
			return $this->returnForbiddenResponse("You are not a user.");
		}
		
		# see which fields were included
		$postData = $request->request->all();
		
		# get the current section
		$section = $em->find('AppBundle\Entity\Section', $postData['section']);		
		if(!$section){
			return $this->returnForbiddenResponse("Section ".$postData['section']." does not exist");
		}
		
		# only super users/admins/teacher can make/edit an assignment
		$grader = new Grader($em);		
		if(!$user->hasRole("ROLE_SUPER") && !$user->hasRole("ROLE_ADMIN") && !isTeaching($user, $section)){			
			return $this->returnForbiddenResponse("You do not have permission to make an assignment.");
		}		
		
		# check mandatory fields
		if(!$postData['name'] || !$postData['open_time'] || !$postData['close_time']){
			return $this->returnForbiddenResponse("Not every required field is provided.");			
		} else {
			
			# validate the weight if there is one
			if($postData['weight'] && ($postData['weight'] < 1 || $postData['weight'] % 1 != 0)){
				$this->returnForbiddenResponse("The weight provided - ".$postData['weight']." - is not permitted.");
			}	
		}
		
		
		# create new assignment
		if($postData['assignment'] == 0){
			$assignment = new Assignment();			
		} else {
			$assignment = $em->find('AppBundle\Entity\Assignment', $postData['assignment']);
			
			if(!$assignment || $section != $assignment->section){
				return $this->returnForbiddenResponse("Assignment ".$postData['assignment']." does not exist for the given section.");
			}			
		}
		
		# set the necessary fields
		$assignment->name = $postData['name'];
		$assignment->description = $postData['description'];
		$assignment->section = $section;
		
		# set the times		
		$openTime = DateTime::createFromFormat("m/d/Y H:i:s", $postData['open_time'].":00");
		$closeTime = DateTime::createFromFormat("m/d/Y H:i:s", $postData['close_time'].":00");
		
		if(!$openTime || $openTime->format("m/d/Y H:i") != $postData['open_time']){
			return $this->returnForbiddenResponse("Provided opening time ".$postData['open_time']." is not valid.");
		}
		
		if(!$closeTime || $closeTime->format("m/d/Y H:i") != $postData['close_time']){
			return $this->returnForbiddenResponse("Provided closing time ".$postData['close_time']." is not valid.");
		}
		
		if($postData['cutoff_time']){
			
			$cutoffTime = DateTime::createFromFormat("m/d/Y H:i:s", $postData['cutoff_time'].":00");
			
			if(!$cutoffTime || $cutoffTime->format("m/d/Y H:i") != $postData['cutoff_time']){
			return $this->returnForbiddenResponse("Provided cutoff time. ".$postData['cutoff_time']." is not valid.");
		}
			
		} else {
			$cutoffTime = $closeTime;
		}
		
		#
		
		$assignment->start_time = $openTime;
		$assignment->end_time = $closeTime;
		$assignment->cutoff_time = $cutoffTime;
		
		
		
		
		
		
		$response = new Response(json_encode(array('redirect_url' => $url, 'assignment' => $assignment, 'postData' => $postData)));
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
