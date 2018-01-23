<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Entity\Course;
use AppBundle\Entity\UserSectionRole;
use AppBundle\Entity\Section;
use AppBundle\Entity\Assignment;
use AppBundle\Entity\Team;

use AppBundle\Utils\Grader;
use AppBundle\Utils\Uploader;

use Doctrine\Common\Collections\ArrayCollection;

use \DateTime;
use \DateInterval;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Psr\Log\LoggerInterface;


class ContestController extends Controller {

	public function contestAction($contestId, $roundId) {

		
		$em = $this->getDoctrine()->getManager();

		$user = $this->get('security.token_storage')->getToken()->getUser();

		if(!$user){
			die("USER DOES NOT EXIST");
		}

		# VALIDATION
		$section = $em->find('AppBundle\Entity\Section', $contestId);

		if(!$section || !$section->course->is_contest){
			die("SECTION DOES NOT EXIST!");
		}

		# GET ALL USERS		
		$section_takers = [];
		$section_judges = [];

		foreach($usersectionroles as $usr){
			if($usr->role->role_name == "Takes"){
				$section_takers[] = $usr->user;
			} else if($usr->role->role_num == "Judges"){
				$section_judges[] = $usr->user;
			}
		}
		
		# GATHER SUBMISSIONS
		# get all of the problems to get all of the submissions
		$allprobs = [];
		foreach($section->assignments as $asgn){
			foreach($asgn->problems as $prob){
				$allprobs[] = $prob;
			}
		}

		$grader = new Grader($em);
		$elevatedUser = $grader->isJudging($user, $section) || $user->hasRole("ROLE_SUPER") || $user->hasRole("ROLE_ADMIN");

		// get the current assignment
		if($roundId == 0){
			$contest = $section->assignments[0]; // TODO: SWITCH TO 1
			$practice = $section->assignments[0];
		} else {
			$contest = $em->find("AppBundle\Entity\Assignment", $roundId);
			$practice = null;
			
			foreach($contest->section->assignments as $asgn){
				if($asgn != $contest){
					$practice = $asgn;
					break;
				}
			}

			if(!$contest){
				die("Contest does not exist!");
			}			
		}
		
		$leaderboard = $grader->getLeaderboard($user, $contest);
		
		
		return $this->render('contest/hub.html.twig', [
			'section' => $section,
			'grader' => $grader,
			'leaderboard' => $leaderboard, 
			
			'contest' => $contest,
			'practice' => $practice,
			
			'assignments' => $section->assignments,

			'section_takers' => $section_takers,
			'section_judges' => $section_judges,
		]);
    }
	
	
	public function problemAction($contestId, $roundId, $problemId) {
		
		die("problemAction");
		
		return $this->render('contest/problem.html.twig', [
			'user' => $user,
			'team' => $team,
			'section' => $assignment_entity->section,
			'assignment' => $assignment_entity,
			'problem' => $problem_entity,

			'languages' => $languages,
			'usersectionrole' => $usersectionrole,
			'grader' => new Grader($em),
			
			'attempts_remaining' => $attempts_remaining,
			
			'best_submission' => $best_submission,
			'last_submission' => $last_submission,
			'all_submissions' => $all_submissions,

			'default_code' => $default_code,
			'ace_modes' => $ace_modes,
			'filetypes' => $filetypes,
		]);
    }
	
	public function judgingAction($contestId, $roundId){
	
		die("judgingAction");

		return new Response();		
	}	
		
	public function contestEditAction($contestId) {
		
		
		
		die("contestEditAction");
		
		if($assignment){
			$di = date_diff($assignment->end_time, $assignment->freeze_time, true);
		
			$hoursLeft = $di->format("%a")*24+$di->format("%h");
			$minutesLeft = $di->format("%i");
		}
		
		return $this->render('contest/edit.html.twig', [
			"assignment" => $assignment,
			"section" => $section,
			"edit" => true,
			"students" => $students,
			
			"hoursLeft" => $hoursLeft,
			"minutesLeft" => $minutesLeft,
		]);
	}
	
	public function modifyPostAction(){
		
		return $this->returnForbiddenResponse("modifyPostAction");
		
		# CONTEST SETTINGS OVERRIDE
		if($section->course->is_contest){
					
			# set cutoff time to end time
			$assignment->cutoff_time = clone $assignment->end_time;
			$assignment->penalty_per_day = 0;
			$assignment->weight = 1;
			$assignment->is_extra_credit = false;			
					
			# validate everything
			$penalty_per_wrong_answer = trim($postData['penalty_per_wrong_answer']);
			if(!is_numeric($penalty_per_wrong_answer) || (int)$penalty_per_wrong_answer < 0){					
				return $this->returnForbiddenResponse("The provided penalty_per_wrong_answer ".$postData['penalty_per_wrong_answer']." is not permitted.");
			}

			$penalty_per_compile_error = trim($postData['penalty_per_compile_error']);
			if(!is_numeric($penalty_per_compile_error) || (int)$penalty_per_compile_error < 0){					
				return $this->returnForbiddenResponse("The provided penalty_per_compile_error ".$postData['penalty_per_compile_error']." is not permitted.");
			}

			$penalty_per_time_limit = trim($postData['penalty_per_time_limit']);
			if(!is_numeric($penalty_per_time_limit) || (int)$penalty_per_time_limit < 0){					
				return $this->returnForbiddenResponse("The provided penalty_per_time_limit ".$postData['penalty_per_time_limit']." is not permitted.");
			}

			$penalty_per_runtime_error = trim($postData['penalty_per_runtime_error']);
			if(!is_numeric($penalty_per_runtime_error) || (int)$penalty_per_runtime_error < 0){					
				return $this->returnForbiddenResponse("The provided penalty_per_runtime_error ".$postData['penalty_per_runtime_error']." is not permitted.");
			}			
			
			$freezeHours = (int)trim($postData['freeze_time_hours']);
			$freezeMins = (int)trim($postData['freeze_time_mins']);
			
			$di = DateInterval::createFromDateString($freezeHours." hours + ".$freezeMins." minutes");
		
			$assignment->freeze_time = clone $assignment->end_time;
			$freezeTime = $assignment->freeze_time->sub($di);
			
			if(!$freezeTime or $freezeTime < $assignment->start_time){
				return $this->returnForbiddenResponse("Provided freeze time ".$postData['freeze_time']." is not valid.");
			}
			
			//return $this->returnForbiddenResponse($freezeTime->format("m/d/Y H:i:s"));
			$assignment->penalty_per_wrong_answer = (int)$penalty_per_wrong_answer;
			$assignment->penalty_per_compile_error = (int)$penalty_per_compile_error;
			$assignment->penalty_per_time_limit = (int)$penalty_per_time_limit;
			$assignment->penalty_per_runtime_error = (int)$penalty_per_runtime_error;
		}
		
		
		return new Response();
		
	}
	
	
	public function problemEditAction($contestId, $roundId, $problemId) {
		
		die("problemEditAction");

		return $this->render('contest/problem_edit.html.twig', [
			'languages' => $languages,
			'section' => $section,
			'assignment' => $assignment,
			'problem' => $problem,
			
			'default_code' => $default_code,
			'ace_modes' => $ace_modes,
			'filetypes' => $filetypes,
			
			'recommendedSlaves' => $recommendedSlaves,
		]);
	}	

	public function resultAction($submission_id){
		
		die("resultAction");
		
		return $this->render('contest/result.html.twig', []);
		
	}
	
	private function returnForbiddenResponse($message){		
		$response = new Response($message);
		$response->setStatusCode(Response::HTTP_FORBIDDEN);
		return $response;
	}
}

?>
