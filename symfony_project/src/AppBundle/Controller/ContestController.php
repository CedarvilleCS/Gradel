<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Entity\Course;
use AppBundle\Entity\UserSectionRole;
use AppBundle\Entity\Section;
use AppBundle\Entity\Assignment;
use AppBundle\Entity\Team;
use AppBundle\Entity\Trial;

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
			die("SECTION (CONTEST) DOES NOT EXIST!");
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
			$contest = $section->assignments[1];
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
		}
		
		if(!$contest || $contest->section != $section){
			die("Contest does not exist!");
		}	
		
		$leaderboard = $grader->getLeaderboard($user, $contest);
		
		
		return $this->render('contest/hub.html.twig', [
			'section' => $section,
			'grader' => $grader,
			'leaderboard' => $leaderboard, 
			
			'elevatedUser' => $elevatedUser,
			
			'contest' => $contest,
			'practice' => $practice,

			'section_takers' => $section_takers,
			'section_judges' => $section_judges,
		]);
    }
	
	/* contest_problem route */
	public function problemAction($contestId, $roundId, $problemId) {
			
		$em = $this->getDoctrine()->getManager();

		$user = $this->get('security.token_storage')->getToken()->getUser();

		if(!$user){
			die("USER DOES NOT EXIST");
		}

		# VALIDATION
		$section = $em->find('AppBundle\Entity\Section', $contestId);
		if(!$section || !$section->course->is_contest){
			die("CONTEST DOES NOT EXIST!");
		}
		
		$assignment = $em->find('AppBundle\Entity\Assignment', $roundId);		
		if(!$assignment || $assignment->section != $section){
			die("ASSIGNMENT DOES NOT EXIST!");
		}
		
		$problem = $em->find('AppBundle\Entity\Problem', $problemId);		
		if(!$problem || $problem->assignment != $assignment){
			die("PROBLEM DOES NOT EXIST!");
		}
		
		// user must be enrolled in the contest or a super user to view this contest problem
		$user_role = $em->getRepository('AppBundle\Entity\UserSectionRole')->findBy([
			'user' => $user,
			'section' => $section,
		]);
		
		
		if(!($user_role || $user->hasRole("ROLE_SUPER") || $user->hasRole("ROLE_ADMIN"))){
			die("YOU ARE NOT ALLOWED TO VIEW THIS SECTION");
		}
		
		// get JSON info for language info
		$problem_languages = $problem->problem_languages->toArray();
		
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
				
		$grader = new Grader($em);
		$team = $grader->getTeam($user, $section);
		// get the list of all submissions by the team/user
		if($team){
			$all_submissions = $em->getRepository('AppBundle\Entity\Submission')->findBy([
				'team' => $team,
				'problem' => $problem,
			], ['timestamp'=>'DESC']);
		} else {
			$all_submissions = $em->getRepository('AppBundle\Entity\Submission')->findBy([
				'user' => $user,
				'problem' => $problem,
			], ['timestamp'=>'DESC']);
		}
		
		// get the trial for the problem
		$trial = $em->getRepository('AppBundle\Entity\Trial')->findOneBy([
			'user' => $user,
			'problem' => $problem,
		]);
				
		return $this->render('contest/problem.html.twig', [
			'user' => $user,
			'team' => $team,
			
			'section' => $section,
			'contest' => $assignment,
			'problem' => $problem,
			
			'trial' => $trial,

			'grader' => $grader,
			
			'all_submissions' => $all_submissions,

			'languages' => $languages,
			'default_code' => $default_code,
			'ace_modes' => $ace_modes,
			'filetypes' => $filetypes,
		]);
    }
	
	public function judgingAction($contestId, $roundId){
		
		$em = $this->getDoctrine()->getManager();

		$user = $this->get('security.token_storage')->getToken()->getUser();
		if(!$user){
			die("USER DOES NOT EXIST");
		}
		$section = $em->find('AppBundle\Entity\Section', $contestId);
		if(!$section || !$section->course->is_contest){
			die("SECTION (CONTEST) DOES NOT EXIST!");
		}
		
		$contest = $em->find('AppBundle\Entity\Assignment', $roundId);
		if(!$contest || $contest->section != $section){
			die("ASSIGNMENT (ROUND) DOES NOT EXIST!");
		}
		
		
		$grader = new Grader($em);
		
		$elevatedUser = ($user->hasRole("ROLE_SUPER") || $user->hasRole("ROLE_ADMIN") || $grader->isJudging($user, $section));
		
		if(!$elevatedUser){
			die("YOU ARE NOT ALLOWED TO ACCESS THIS PAGE");
		}
		
		return $this->render('contest/judging.html.twig', [
			'section' => $section,
			'grader' => $grader,
						
			'elevatedUser' => $elevatedUser,
						
			'contest' => $contest,
			
			'pending_submissions' => $pending_submissions,

			'section_takers' => $section_takers,
			'section_judges' => $section_judges,
		]);	
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
	
	public function modifyProblemPostAction(Request $request){
		
		return $this->returnForbiddenResponse("modifyProblemPostAction");
		
		return new Response();
	}
	
	public function modifyContestPostAction(Request $request){
		
		return $this->returnForbiddenResponse("modifyContestPostAction");
		
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

	public function resultAction($contestId, $roundId, $problemId, $resultId){
		
		$em = $this->getDoctrine()->getManager();

		$user = $this->get('security.token_storage')->getToken()->getUser();

		if(!$user){
			die("USER DOES NOT EXIST");
		}
		
		
		$section = $em->find('AppBundle\Entity\Section', $contestId);
		if(!$section || !$section->course->is_contest){
			die("CONTEST DOES NOT EXIST!");
		}
		
		$assignment = $em->find('AppBundle\Entity\Assignment', $roundId);		
		if(!$assignment || $assignment->section != $section){
			die("ASSIGNMENT DOES NOT EXIST!");
		}
		
		$problem = $em->find('AppBundle\Entity\Problem', $problemId);		
		if(!$problem || $problem->assignment != $assignment){
			die("PROBLEM DOES NOT EXIST!");
		}	

		$submission = $em->find('AppBundle\Entity\Submission', $resultId);		
		if(!$submission || $submission->problem != $problem){
			die("SUBMISSION DOES NOT EXIST");
		}
		
		return $this->render('contest/result.html.twig', [
		
			'problem' => $problem,
			'contest' => $assignment,
			'submission' => $submission,
		
			'grader' => new Grader($em),
		]);
		
	}
	
	
	public function pollContestAction(Request $request){
		
		return $this->returnForbiddenResponse("pollContestAction");
		
		return new Response();
	}
	
	public function pollJudgingAction(Request $request){
		
		$em = $this->getDoctrine()->getManager();
		$grader = new Grader($em);

		$user = $this->get('security.token_storage')->getToken()->getUser();
		if(!$user){
			return $this->returnForbiddenResponse('User does not exist.');
		}
		
		# post data
		$postData = $request->request->all();
		
		$contest = $em->find('AppBundle\Entity\Assignment', $postData['contestId']);		
		if(!$contest){
			return $this->returnForbiddenResponse('Contest ID is not valid.');
		}		
		
		# validation
		if(!($user->hasRole("ROLE_SUPER") || $user->hasRole("ROLE_ADMIN") || $grader->isJudging($user, $contest->section))){
			return $this->returnForbiddenResponse("You are not allowed to poll this contest");
		}
		
		# get the pending submissions
		$qb_allsubs = $em->createQueryBuilder();
		$qb_allsubs->select('s')
			->from('AppBundle\Entity\Submission', 's')
			->where('s.problem IN (?1)')
			->andWhere('s.pending_status = ?2')
			->orderBy('s.timestamp', 'ASC')
			->setParameter(1, $contest->problems->toArray())
			->setParameter(2, 0);
		$subs_query = $qb_allsubs->getQuery();
		$pending_submissions = $subs_query->getResult();
		
//		return $this->returnForbiddenResponse(json_encode($pending_submissions[0])." ");
		
		$qb_revsubs = $em->createQueryBuilder();
		$qb_revsubs->select('s')
			->from('AppBundle\Entity\Submission', 's')
			->where('s.problem IN (?1)')
			->andWhere('s.pending_status != ?2')
			->orderBy('s.timestamp', 'ASC')
			->setParameter(1, $contest->problems->toArray())
			->setParameter(2, 0);
		$rev_subs_query = $qb_revsubs->getQuery();
		$reviewed_submissions = $rev_subs_query->getResult();
		
		$response = new Response(json_encode([
			'pending_submissions' => $pending_submissions,
			'reviewed_submissions' => $reviewed_submissions,
		]));
			
		
		$response->headers->set('Content-Type', 'application/json');
		$response->setStatusCode(Response::HTTP_OK);

		return $response;
	}
	
	public function submissionJudgingAction(Request $request){
		
		$em = $this->getDoctrine()->getManager();

		$user = $this->get('security.token_storage')->getToken()->getUser();
		if(!$user){
			die("USER DOES NOT EXIST");
		}
		# see which fields were included	
		$postData = $request->request->all();
		
		$submission = $em->find('AppBundle\Entity\Submission', $postData['submissionId']);
		
		$grader = new Grader($em);
		
		# validation
		if(!($user->hasRole("ROLE_SUPER") || $user->hasRole("ROLE_ADMIN") || $grader->isJudging($user, $submission->problem->assignment->section))){
			return $this->returnForbiddenResponse("You are not allowed to edit this submission");
		}
		
		if(!$submission){
			return $this->returnForbiddenResponse('Submission ID is not valid.');
		}
		
		
		// check to make sure the submission hasn't been claimed
		// ************************* RACE CONDITIONS *************************
		if($submission->pending_status > 1 && !$postData['override']){
			return $this->returnForbiddenResponse("Submission has already been reviewed");
		}
					
		$reviewed = true;
		if($postData['type'] == "wrong"){
			
			// override the submission to wrong
			if($submission->isCorrect(true)){
				
				$submission->wrong_override = true;				
				$submission->correct_override = false;
			} else {
				
				$submission->wrong_override = false;
				$submission->correct_override = false;
			}
			
		} else if($postData['type'] == "correct"){
			
			// override the submission to correct
			if($submission->isCorrect(true)){
				
				$submission->wrong_override = false;
				$submission->correct_override = false;	
				
			} else {
				
				$submission->wrong_override = false;
				$submission->correct_override = true;					
			}
			
		} else if($postData['type'] == "delete"){
				
			// delete the submission
			$em->remove($submission);			
				
		} else if($postData['type'] == "formatting"){
					
			// add formatting message to submission
			$submission->judge_message = "Formatting Error";
					
		} else if($postData['type'] == "message"){
			
			$message = $postData['message'];
			
			// add custom message to submission
			if(!isset($message) || trim($message) == ""){
				$submission->judge_message = NULL;
			} else {
				$submission->judge_message = trim($postData['message']);	
			}			
						
		} else if($postData['type'] == "claimed"){
			
			$reviewed = false;			
			
			if($submission->pending_status > 0){
				return $this->returnForbiddenResponse("Submission has already been claimed");
			}	
			
			$submission->pending_status = 1;
			
		} else if($postData['type'] == "unclaimed"){
			
			$reviewed = false;			
			
			if($submission->pending_status < 1){
				return $this->returnForbiddenResponse("Submission has already been un-claimed");
			}	
			
			$submission->pending_status = 0;
			
			
		} else {
			return $this->returnForbiddenResponse("Type of judging command not allowed");
		}

		
		if($reviewed){
			$submission->pending_status = 2;
		}
		
		$submission->reviewer = $user;
		
		$submission->edited_timestamp = new \DateTime("now");
		
		$response = new Response(json_encode([
			'id' => $submission->id,
			'reviewed' => $reviewed, 
		]));
		
		$em->flush();		
		
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
