<?php

namespace AppBundle\Controller;

use \DateTime;
use \DateInterval;

use AppBundle\Entity\User;
use AppBundle\Entity\Course;
use AppBundle\Entity\UserSectionRole;
use AppBundle\Entity\Role;
use AppBundle\Entity\Query;
use AppBundle\Entity\Section;
use AppBundle\Entity\Assignment;
use AppBundle\Entity\Submission;
use AppBundle\Entity\Problem;
use AppBundle\Entity\Team;
use AppBundle\Entity\Testcase;
use AppBundle\Entity\ProblemLanguage;

use AppBundle\Utils\Grader;

use Doctrine\Common\Collections\ArrayCollection;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class ContestPagesController extends Controller {

	public function contestAction($contestId, $roundId) {
	
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
		
		$grader = new Grader($em);
		$elevatedUser = $grader->isJudging($user, $section) || $user->hasRole("ROLE_SUPER") || $user->hasRole("ROLE_ADMIN");

		// elevated or taking and active
		if( !($elevatedUser || ($grader->isTaking($user, $section) && $section->isActive())) ){
			
			return $this->returnForbiddenResponse("PERMISSION DENIED");
		}

		# GET CURRENT CONTEST		
		$allContests = $section->assignments->toArray();	
		$currTime = new \DateTime('now');
		
		$current = null;
		// decide the round for the users
		if ($roundId == 0){
			
			// if the round was not provided, we need to default to the proper contest for them
			// get the one that will start next/is currently going on
			foreach($allContests as $cont) {
				
				// choose the one that ends next
				if ($currTime <= $cont->end_time) {
					$current = $cont;
					break;
				}
			}
			
			// if all the contest are past, get the final one
			if(!$current){
				$current = $allContests[count($allContests)-1];
			}
			
		}
		// use the round provided
		else {			
			$current = $em->find("AppBundle\Entity\Assignment", $roundId);
		}
	
		if(!$current || $current->section != $section){
			die("ROUND DOES NOT EXIST");
		}

		// check to see if you need to populate the post contest
		if($current->post_contest){

			$previous = $allContests[count($allContests) - 2];
			
			if(!$current->is_cloned && $previous->isFinished() && $current->isActive()){
					
				$current->is_cloned = true;

				# create problems
				$newProbs = [];
				$prevProbs = $previous->problems->toArray();
				foreach($prevProbs as $prevProb){
					
					$prb = clone $prevProb;				
					$prb->assignment = $current;

					$em->persist($prb);

					$newProbs[$prevProb->id] = $prb;
				}				
				
				# create teams
				$prevTeams = $previous->teams->toArray();
				foreach($prevTeams as $prevTeam){

					$prevSubs = $prevTeam->submissions->toArray();

					foreach($prevTeam->users as $prevUser){

						$tm = new Team();
						$tm->assignment = $current;
						$tm->name = $prevUser->getFullName();
						$tm->workstation_number = 0;
						$tm->users->add($prevUser);

						$em->persist($tm);

						foreach($prevSubs as $prevSub){
							$sb = clone $prevSub;
							$sb->problem = $newProbs[$sb->problem->id];
							$sb->team = $tm;							

							$em->persist($sb);
						}
					}
				}
			
				# create queries/answers
				$prevQueries = $previous->queries->toArray();
				foreach($prevQueries as $prevQuery){

					$qry = clone $prevQuery;
					$qry->assignment = $current;

					$current->queries->add($qry);
				}

				$em->persist($current);
				$em->flush();

				//return $this->returnForbiddenResponse(json_encode($current));
			}
		}
		
		$team = $grader->getTeam($user, $current);
				
		# set open/not open
		if($elevatedUser || ($current->start_time <= $currTime)){
			$contest_open = true;
		} else {
			$contest_open = false;
		}
		
		# GET ALL USERS
		$qb_user = $em->createQueryBuilder();
		$qb_user->select('usr')
			->from('AppBundle\Entity\UserSectionRole', 'usr')
			->where('usr.section = ?1')
			->setParameter(1, $section);

		$user_query = $qb_user->getQuery();
		$usersectionroles = $user_query->getResult();

		$section_takers = [];
		foreach($usersectionroles as $usr){
			if($usr->role->role_name == "Takes"){
				$section_takers[] = $usr->user;
			} else if($usr->role->role_num == "Judges"){
				$section_takers[] = $usr->user;
			}
		}
		
		return $this->render('contest/hub.html.twig', [
			'user' => $user,
			'team' => $team,
			
			'section' => $section,
			
			'grader' => $grader,		

			'user_impersonators' => $section_takers,
			
			'current_contest' => $current,
			
			'contest_open' => $contest_open,
			
			'contests' => $allContests,
			'elevatedUser' => $elevatedUser,
		]);
    }

	public function problemAction($contestId, $roundId, $problemId) {
				
		$em = $this->getDoctrine()->getManager();

		$user = $this->get('security.token_storage')->getToken()->getUser();

		if(!$user){
			die("USER DOES NOT EXIST");
		}

		# VALIDATION
		$section = $em->find('AppBundle\Entity\Section', $contestId);
		if(!$section || !$section->course->is_contest){
			die("404 - CONTEST DOES NOT EXIST!");
		}
		
		$assignment = $em->find('AppBundle\Entity\Assignment', $roundId);		
		if(!$assignment || $assignment->section != $section){
			die("404 - ASSIGNMENT DOES NOT EXIST!");
		}
		
		$problem = $em->find('AppBundle\Entity\Problem', $problemId);		
		if(!$problem || $problem->assignment != $assignment){
			die("404 - PROBLEM DOES NOT EXIST!");
		}
				
		$grader = new Grader($em);
		$elevatedUser = $grader->isJudging($user, $section) || $user->hasRole("ROLE_SUPER") || $user->hasRole("ROLE_ADMIN");

		// elevated or taking and open
		if( !($elevatedUser || ($grader->isTaking($user, $section) && $assignment->isOpened())) ){
			
			return $this->redirectToRoute('contest', ['contestId' => $section->id, 'roundId' => $assignment->id]);
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
		$team = $grader->getTeam($user, $assignment);
		
		// get the list of all submissions by the team/user
		if($team){
			$all_submissions = $em->getRepository('AppBundle\Entity\Submission')->findBy([
				'team' => $team,
				'problem' => $problem,
				'is_completed' => true,
			], ['timestamp'=>'DESC']);
		}
		// no team, so it is just a user (judge)
		else {
			$all_submissions = $em->getRepository('AppBundle\Entity\Submission')->findBy([
				'user' => $user,
				'problem' => $problem,
				'is_completed' => true,
			], ['timestamp'=>'DESC']);
		}
		
		// get the trial for the problem
		$trial = $em->getRepository('AppBundle\Entity\Trial')->findOneBy([
			'user' => $user,
			'problem' => $problem,
		]);
		
		# get the queries
		if($elevatedUser){
			$extra_query = "OR 1=1";
		} else {
			$extra_query = "";
		}
		
		$qb_queries = $em->createQueryBuilder();
		$qb_queries->select('q')
			->from('AppBundle\Entity\Query', 'q')
			->where('q.problem = (?1)')
			->andWhere('q.asker = ?2 OR q.asker IS NULL '.$extra_query)
			->orderBy('q.timestamp', 'ASC')
			->setParameter(1, $problem)
			->setParameter(2, $team);
		$query_query = $qb_queries->getQuery();
		$queries = $query_query->getResult();		
		
		
		# set open/not open
		$currTime = new \DateTime("now");
		if($elevatedUser || ($assignment->start_time <= $currTime)){
			$contest_open = true;
		} else {
			$contest_open = false;
		}
		
		if(!$contest_open){
			
			return $this->redirectToRoute('contest', ['contestId' => $assignment->section->id, 'roundId' => $assignment->id]);
		}
		
		# submission updating trial
		if(isset($_GET["submissionId"])){
			
			$submission = $em->find("AppBundle\Entity\Submission", $_GET["submissionId"]);
			
			$sameTeam = true;
			$sameUser = true;
			if($submission->team){
				
				$team = $grader->getTeam($user, $submission->problem->assignment);
				
				$sameTeam = ($team == $submission->team);
				
			} else {
				
				$sameUser = ($user == $submission->user);				
			}
			
			if(!$elevatedUser && !($sameTeam || $sameUser || $submission->problem == $problem)){
				die("You are not allowed to edit this submission on this problem!");
			}
			
			if(!$trial){
				$trial = new Trial();
				
				$trial->user = $user;
				$trial->problem = $problem;
				$trial->language = $submission->language;			
				$trial->show_description = true;
				
				$em->persist($trial);
			}
			
			$trial->file = $submission->submitted_file;
						
			
			$trial->filename = $submission->filename;
			$trial->main_class = $submission->main_class_name;
			$trial->package_name = $submission->package_name;
			$trial->last_edit_time = new \DateTime("now");
		}
										
		return $this->render('contest/problem.html.twig', [
			'user' => $user,
			'team' => $team,
			
			'section' => $section,
			
			'current_contest' => $assignment,
			'contest_open' => $contest_open,
			
			'problem' => $problem,
			'trial' => $trial,
			
			'queries' => $queries, 

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
			die("CONTEST DOES NOT EXIST!");
		}
		
		$allContests = $section->assignments;
		
		# get the current contest (see contestAction for a duplicate function)
		$current = $em->find("AppBundle\Entity\Assignment", $roundId);
		
		if(!$current || $current->section != $section){
			die("404 - CONTEST DOES NOT EXIST");
		}	
		
		$grader = new Grader($em);		
		$elevatedUser = $grader->isJudging($user, $section) || $user->hasRole("ROLE_SUPER") || $user->hasRole("ROLE_ADMIN");

		// elevated
		if( !($elevatedUser) ){
			
			return $this->returnForbiddenResponse("PERMISSION DENIED");
		}
		
		return $this->render('contest/judging.html.twig', [
			'section' => $section,
			'grader' => $grader,
						
			'elevatedUser' => $elevatedUser,
						
			'current_contest' => $current,
			
			'contests' => $allContests,
			
			'contest_open' => true,
			
			'pending_submissions' => $pending_submissions,

			'section_takers' => $section_takers,
			'section_judges' => $section_judges,
		]);	
	}	
		
	public function problemEditAction($contestId, $roundId, $problemId) {
				
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
		
		if($problemId != 0){
		
			$problem = $em->find('AppBundle\Entity\Problem', $problemId);
			if(!$problem || $problem->assignment != $contest){
				die("PROBLEM DOES NOT EXIST!");
			}
			
		} else {
			
			$problem = null;			
		}
		
		$grader = new Grader($em);
		$elevatedUser = $grader->isJudging($user, $section) || $user->hasRole("ROLE_SUPER") || $user->hasRole("ROLE_ADMIN");

		// elevated 
		if( !($elevatedUser) ){			
			return $this->returnForbiddenResponse("PERMISSION DENIED");
		}
		
		$default_code = [];
		$ace_modes = [];
		$filetypes = [];
		
		$languages = $em->getRepository('AppBundle\Entity\Language')->findAll();		
		foreach($languages as $l){
			
			$ace_modes[$l->name] = $l->ace_mode;
			$filetypes[str_replace(".", "", $l->filetype)] = $l->name;
			
			// either get the default code from the problem or from the overall default
			$default_code[$l->name] = $l->deblobinateDefaultCode();
		}		
		
		//die(json_encode($problem, JSON_PRETTY_PRINT));		
		
		return $this->render('contest/problem_edit.html.twig', [
			'contest' => $contest,
			'current' => $contest,
			'current_contest' => $contest, 
			
			'problem' => $problem,

			'edit_route' => true,
			
			'languages' => $languages, 
			
			'ace_modes' => $ace_modes,
			'filetypes' => $filetypes,
			'default_code' => $default_code,
		]);
	}
	
	public function contestEditAction($contestId) {
		
		$em = $this->getDoctrine()->getManager();

		$user = $this->get('security.token_storage')->getToken()->getUser();

		if(!$user){
			die("USER DOES NOT EXIST");
		}

		# VALIDATION
		if($contestId != 0){
			$section = $em->find('AppBundle\Entity\Section', $contestId);

			if(!$section || !$section->course->is_contest){
				die("404 - SECTION (CONTEST) DOES NOT EXIST!");
			}
			
			$course = $section->course;
			
			$grader = new Grader($em);
			$elevatedUser = $grader->isJudging($user, $section) || $user->hasRole("ROLE_SUPER") || $user->hasRole("ROLE_ADMIN");
					
			# get the judges
			$judgeRole = $em->getRepository("AppBundle\Entity\Role")->findOneBy([
				'role_name' => 'Judges',
			]);
			
			$judges = $em->getRepository('AppBundle\Entity\UserSectionRole')->findBy([
				'section' => $section,
				'role' => $judgeRole,
			]);
			
			# get freeze time diff
			$di = $section->assignments[1]->end_time->diff($section->assignments[1]->freeze_time);
		
			$freeze_diff_minutes = $di->i;
			$freeze_diff_hours = ($di->days * 24) + $di->h;
		
		} else {
			
			// TODO FIX THIS LINE
			$course = $em->find("AppBundle\Entity\Course", $_GET['courseId']);
			
			if(!$course->is_contest){
				return $this->returnForbiddenResponse('PERMISSION DENIED');
			}
			
			$section = null;
			$freeze_diff_hours = 1;
			$freeze_diff_minutes = 0;
			
			$judges = [];
			$elevatedUser = $user->hasRole("ROLE_ADMIN") || $user->hasRole("ROLE_SUPER");
		}
		
		if( !($elevatedUser) ){
			
			return $this->returnForbiddenResponse("PERMISSION DENIED");
		}

		$languages = $em->getRepository("AppBundle\Entity\Language")->findAll();
		
		return $this->render('contest/edit.html.twig', [
			'course' => $course,
			'section' => $section,
			
			'freeze_diff_hours' => $freeze_diff_hours,
			'freeze_diff_minutes' => $freeze_diff_minutes,
			
			'languages' => $languages,

			'judges' => $judges,
			
			"elevatedUser" => $elevatedUser,
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
			die("404 - CONTEST DOES NOT EXIST!");
		}
		
		$assignment = $em->find('AppBundle\Entity\Assignment', $roundId);		
		if(!$assignment || $assignment->section != $section){
			die("404 - ASSIGNMENT DOES NOT EXIST!");
		}
		
		$problem = $em->find('AppBundle\Entity\Problem', $problemId);		
		if(!$problem || $problem->assignment != $assignment){
			die("404 - PROBLEM DOES NOT EXIST!");
		}	

		$submission = $em->find('AppBundle\Entity\Submission', $resultId);		
		if(!$submission || $submission->problem != $problem || !$submission->is_completed){
			die("404 - SUBMISSION DOES NOT EXIST");
		}

		$grader = new Grader($em);
		$elevatedUser = $grader->isJudging($user, $section) || $user->hasRole("ROLE_SUPER") || $user->hasRole("ROLE_ADMIN");

		$team = $grader->getTeam($user, $assignment);
		
		// elevated or on submission team
		if( !($elevatedUser || $team == $submission->team) ){
			
			return $this->returnForbiddenResponse("PERMISSION DENIED");
		}
		
		$ace_mode = $submission->language->ace_mode;
		
		return $this->render('contest/result.html.twig', [
		
			'user' => $user,
			'team' => $team,
		
			'problem' => $problem,
			'current_contest' => $assignment,

			'section' => $assignment->section,

			'submission' => $submission,
			
			'ace_mode' => $ace_mode,
			
			'contest_open' => true,

			'result_route' => true,
		
			'grader' => new Grader($em),
		]);
		
	}
	
	public function scoreboardAction($contestId, $roundId){
				
		$em = $this->getDoctrine()->getManager();
		$user = $this->get('security.token_storage')->getToken()->getUser();
		$grader = new Grader($em);
		
		if(!$user){
			die("404 - USER DOES NOT EXIST");
		}

		# VALIDATION
		$section = $em->find('AppBundle\Entity\Section', $contestId);

		if(!$section || !$section->course->is_contest){
			die("404 - CONTEST DOES NOT EXIST!");
		}
		
		$assignment = $em->find('AppBundle\Entity\Assignment', $roundId);
		
		if(!$assignment || $assignment->section != $section){
			die("404 - ROUND DOES NOT EXIST!");
		}
		
		if( is_object($user) ){
			
			$elevatedUser = $grader->isJudging($user, $section) || $user->hasRole("ROLE_SUPER") || $user->hasRole("ROLE_ADMIN");
			$team = $grader->getTeam($user, $assignment);
			
		} else {
			
			$elevatedUser = false;
			$team = null;
		}		
		
		# elevated or section active
		if( !($elevatedUser || $section->isActive()) ){			
			return $this->returnForbiddenResponse("PERMISSION DENIED");
		}
		
		
		# set open/not open
		if($elevatedUser || ($current->start_time <= $currTime)){
			$contest_open = true;
		} else {
			$contest_open = false;
		}
		
		return $this->render('contest/scoreboard.html.twig', [
			'user' => $user,
			'team' => $team,
			
			'section' => $section,
			'grader' => $grader, 			
			
			'current_contest' => $assignment,
			'contest_open' => $contest_open,
			
			'elevatedUser' => $elevatedUser,
		]);
		
	}
	
	private function returnForbiddenResponse($message){		
		$response = new Response($message);
		$response->setStatusCode(Response::HTTP_FORBIDDEN);
		return $response;
	}

}

?>
