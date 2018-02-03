<?php

namespace AppBundle\Controller;

use \DateTime;
use \DateInterval;

use AppBundle\Entity\User;
use AppBundle\Entity\Course;
use AppBundle\Entity\UserSectionRole;
use AppBundle\Entity\Role;
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
		
		$grader = new Grader($em);
		$team = $grader->getTeam($user, $section);

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

		$grader = new Grader($em);
		$elevatedUser = $grader->isJudging($user, $section) || $user->hasRole("ROLE_SUPER") || $user->hasRole("ROLE_ADMIN");
				
		# GET CURRENT CONTEST		
		$allContests = $section->assignments->toArray();	
		$currTime = new \DateTime('now');
		
		$current = null;
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
			
			// if you aren't in a contest, get the one the final one
			if(!$current){
				$current = $allContests[count($allContests)-1];
			}
			
		} else {
			
			$current = $em->find("AppBundle\Entity\Assignment", $roundId);
		}
	
		if(!$current || $current->section != $section){
			die("Contest does not exist!");
		}	
		
		$leaderboard = $grader->getLeaderboard($user, $current);
		
		# get the queries
		if($grader->isJudging($user, $section) || $user->hasRole("ROLE_ADMIN") || $user->hasRole("ROLE_SUPER")){
			$extra_query = "OR 1=1";
		} else {
			$extra_query = "";
		}
		
		$qb_queries = $em->createQueryBuilder();
		$qb_queries->select('q')
			->from('AppBundle\Entity\Query', 'q')
			->where('q.assignment = (?1)')
			->andWhere('q.asker = ?2 OR q.asker IS NULL '.$extra_query)
			->orderBy('q.timestamp', 'ASC')
			->setParameter(1, $contest)
			->setParameter(2, $team);
		$query_query = $qb_queries->getQuery();
		$queries = $query_query->getResult();
		
		# set open/not open
		if($elevatedUser || ($current->start_time <= $currTime)){
			$contest_open = true;
		} else {
			$contest_open = false;
		}
		
		return $this->render('contest/hub.html.twig', [
			'user' => $user,
			'team' => $team,
			
			'section' => $section,
			'grader' => $grader,
			'leaderboard' => $leaderboard, 			
			
			'current_contest' => $current,			
			
			'contest_open' => $contest_open,
			
			'queries' => $queries,
			'contests' => $allContests,
			'elevatedUser' => $elevatedUser,
						
			'section_takers' => $section_takers,
			'section_judges' => $section_judges,
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
		$team = $grader->getTeam($user, $assignment);
		
		// get the list of all submissions by the team/user
		if($team){
			$all_submissions = $em->getRepository('AppBundle\Entity\Submission')->findBy([
				'team' => $team,
				'problem' => $problem,
				'is_completed' => true,
			], ['timestamp'=>'DESC']);
		} else {
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
		if($grader->isJudging($user, $assignment) || $user->hasRole("ROLE_ADMIN") || $user->hasRole("ROLE_SUPER")){
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
			die("SECTION (CONTEST) DOES NOT EXIST!");
		}
		
		$allContests = $section->assignments;
		
		# get the current contest (see contestAction for a duplicate function)
		$current = null;
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
			
			// if you aren't in a contest, get the one the final one
			if(!$current){
				$current = $allContests[count($allContests)-1];
			}
			
		} else {
			
			$current = $em->find("AppBundle\Entity\Assignment", $roundId);
		}
	
		if(!$current || $current->section != $section){
			die("Contest does not exist!");
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
						
			'current_contest' => $current,
			
			'contests' => $allContests,
			
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
		
		
		$languages = $em->getRepository('AppBundle\Entity\Language')->findAll();
		
		
		$default_code = [];
		$ace_modes = [];
		$filetypes = [];
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
			
			'problem' => $problem,
			
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
				die("SECTION (CONTEST) DOES NOT EXIST!");
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
			
			$course = $em->find("AppBundle\Entity\Course", 2);
			
			$section = null;
			$freeze_diff_hours = 1;
			$freeze_diff_minutes = 0;
			
			$judges = [];
			$elevatedUser = $user->hasRole("ROLE_ADMIN") || $user->hasRole("ROLE_SUPER");
		}
		
		
		return $this->render('contest/edit.html.twig', [
			'course' => $course,
			'section' => $section,
			
			'freeze_diff_hours' => $freeze_diff_hours,
			'freeze_diff_minutes' => $freeze_diff_minutes,
			
			'judges' => $judges,
			
			"elevatedUser" => $elevatedUser,
		]);
	}

	public function modifyProblemPostAction(Request $request){
		
		$em = $this->getDoctrine()->getManager();

		$user = $this->get('security.token_storage')->getToken()->getUser();
		if(!$user){
			die("USER DOES NOT EXIST");
		}
		
		# POST DATA
		$postData = $request->request->all();
		
		# ASSIGNMENT/CONTEST
		if(!isset($postData['assignmentId'])){
			return $this->returnForbiddenResponse("assignmentId not provided");
		}
		
		$assignment = $em->find("AppBundle\Entity\Assignment", $postData['assignmentId']);		
		if(!$assignment){
			return $this->returnForbiddenResponse("Assignment with provided id does not exist");
		}
				
		# PROBLEM
		if(isset($postData['problemId'])){
			
			if($postData['problemId'] == 0){

				$problem = new Problem();
				$problem->assignment = $assignment;
				//$em->persist($problem);

			} else {
				
				$problem = $em->find('AppBundle\Entity\Problem', $postData['problemId']);

				if(!$problem || $assignment != $problem->assignment){
					return $this->returnForbiddenResponse("Problem with provided id does not exist");
				}
			}		
			
		} else {			
			return $this->returnForbiddenResponse("problemId not provided");			
		}
		
		# DEFAULT CONTEST SETTINGS
		$problem->version = $problem->version+1;
		$problem->weight = 1;
		$problem->is_extra_credit = false;
		$problem->total_attempts = 0;
		$problem->attempts_before_penalty = 0;
		$problem->penalty_per_attempt = 0;
		$problem->stop_on_first_fail = true;
		$problem->response_level = "None";
		$problem->display_testcaseresults = false;
		$problem->testcase_output_level = "None";
		$problem->extra_testcases_display = false;	
		$problem->slaves = new ArrayCollection();
		$problem->master = null;	

		# NAME AND DESCRIPTION
		if(isset($postData['name']) && trim($postData['name']) != "" && isset($postData['description']) && trim($postData['description']) != ""){

			$problem->name = trim($postData['name']);
			$problem->description = trim($postData['description']);
	
		} else {
	
			return $this->returnForbiddenResponse("name and description need to be provided");
		
		}
		
		# TIME LIMIT
		$time_limit = trim($postData['time_limit']);
		if(!is_numeric($time_limit) || $time_limit < 0 || $time_limit != round($time_limit)){					
			return $this->returnForbiddenResponse("time limit provided was not valid");
		}

		$problem->time_limit = $time_limit;
				
		# PROBLEM LANGUAGES
		# remove the old ones
		$problem->problem_languages->clear();
		
		// For now, default the contest languages to just be C++ and Java		
		$languageCPP = $em->getRepository("AppBundle\Entity\Language")->findOneBy([
			'name' => "C++",
		]);
		
		$languageJAVA = $em->getRepository("AppBundle\Entity\Language")->findOneBy([
			'name' => "Java",
		]);

		if(!$languageCPP && !$languageJAVA){
			return $this->returnForbiddenResponse("languages could not be generated properly - this is Timothy's fault");
		}

		$problemLanguageCPP = new ProblemLanguage();
		$problemLanguageJAVA = new ProblemLanguage();

		$problemLanguageCPP->language = $languageCPP;
		$problemLanguageCPP->problem = $problem;
		$problemLanguageJAVA->language = $languageJAVA;
		$problemLanguageJAVA->problem = $problem;
		
		$problem->problem_languages->add($problemLanguageCPP);
		$problem->problem_languages->add($problemLanguageJAVA);
		
		# TESTCASES
		# set the old testcases to null 
		# so they don't go away and can be accessed in the results page
		foreach($problem->testcases as &$testcase){
			$testcase->problem = null;
			$em->persist($testcase);
		}
		
		$newTestcases = new ArrayCollection();
		$count = 1;
		foreach($postData['testcases'] as &$tc){
			
			$tc = (array) $tc;
			
			# build the testcase
			$testcase = new Testcase();
			
			$testcase->problem = $problem;
			$testcase->seq_num = $count;
			$testcase->command_line_input = null;
			$testcase->feedback = null;
			$testcase->weight = 1;
			$testcase->is_extra_credit = false;
			
			if(isset($tc['input']) && trim($tc['input']) != "" && isset($tc['output']) && trim($tc['output']) != "" && isset($tc['sample'])){
				
				$testcase->input = $tc['input'];
				$testcase->correct_output = $tc['output'];
				$testcase->is_sample = ($tc['sample'] == "true");
				
			} else {
				return $this->returnForbiddenResponse("testcase not formatted properly");
			}
		 
			$em->persist($testcase);
			$newTestcases->add($testcase);
			
			
			$count++;
		}
		$problem->testcases = $newTestcases;
		$problem->testcase_counts[] = count($problem->testcases);	

		
		//return $this->returnForbiddenResponse(json_encode($problem));
		
		$em->persist($problem);		
		$em->flush();		
		
		$url = $this->generateUrl('contest_problem', ['contestId' => $problem->assignment->section->id, 'roundId' => $problem->assignment->id, 'problemId' => $problem->id]);
				
		$response = new Response(json_encode([
			'id' => $problem->id,
			'redirect_url' => $url,
			'problem' => $problem,
		]));			
		
		$response->headers->set('Content-Type', 'application/json');
		$response->setStatusCode(Response::HTTP_OK);

		return $response;
	}
	
	public function modifyContestPostAction(Request $request){
				
		$em = $this->getDoctrine()->getManager();

		$user = $this->get('security.token_storage')->getToken()->getUser();
		if(!$user){
			die("USER DOES NOT EXIST");
		}
		
		# POST DATA
		$postData = $request->request->all();
		
		# COURSE
		if(!isset($postData['courseId'])){
			return $this->returnForbiddenResponse("courseId not provided");
		}
		
		$course = $em->find("AppBundle\Entity\Course", $postData['courseId']);		
		if(!$course){
			return $this->returnForbiddenResponse("Course with provided id does not exist");
		}
		
		
		# SECTION 		
		if(!isset($postData['contestId'])){
			
			return $this->returnForbiddenResponse("contestId not provided");
			
		} else if($postData['contestId'] > 0){
			
			$section = $em->find('AppBundle\Entity\Section', $postData['contestId']);
			if(!$section || $section->course != $course || !$section->course->is_contest){
				return $this->returnForbiddenResponse("Contest does not exist.");
			}
			
			$practiceContest = $section->assignments[0];
			$actualContest = $section->assignments[1];
			
		} else {

			$section = new Section();
			
			$practiceContest = new Assignment();
			$actualContest = new Assignment();	

			# set up the section
			$section->course = $course;
			$section->semester = "";
			$section->year = 0;
			$section->is_public = false;
			$section->is_deleted = false;			
			
			# set up the practice contest
			$practiceContest->section = $section;
			$practiceContest->name = "Practice Contest";
			$practiceContest->description = "This is the practice contest.";
			$practiceContest->weight = 1;
			$practiceContest->is_extra_credit = false;
			$practiceContest->penalty_per_day = 0;
			
			# set up the actual contest
			$actualContest->section = $section;
			$actualContest->name = "Actual Contest";
			$actualContest->description = "This is the actual contest.";
			$actualContest->weight = 1;
			$actualContest->is_extra_credit = false;
			$actualContest->penalty_per_day = 0;
			
			$section->assignments->add($practiceContest);
			$section->assignments->add($actualContest);
		}		
		
		
		$grader = new Grader($em);
		
		$elevatedUser = ($user->hasRole("ROLE_SUPER") || $user->hasRole("ROLE_ADMIN") || $grader->isJudging($user, $section));		
		if(!$elevatedUser){
			return $this->returnForbiddenResponse("You are not allowed to edit this contest.");
		}	
		
		# NAME
		if(!isset($postData['contest_name']) || trim($postData['contest_name']) == ""){
			return $this->returnForbiddenResponse("contestId name not provided.");
		}
		
		$section->name = trim($postData['contest_name']);
			
		# PENALTY POINTS
		$penalty_per_wrong_answer = trim($postData['pen_per_wrong']);
		if(!is_numeric($penalty_per_wrong_answer) || $penalty_per_wrong_answer < 0 || $penalty_per_wrong_answer != round($penalty_per_wrong_answer)){					
			return $this->returnForbiddenResponse("The provided penalty_per_wrong_answer ".$postData['penalty_per_wrong_answer']." is not permitted.");
		}

		$penalty_per_compile_error = trim($postData['pen_per_compile']);
		if(!is_numeric($penalty_per_compile_error) || $penalty_per_compile_error < 0 || $penalty_per_compile_error != round($penalty_per_compile_error)){					
			return $this->returnForbiddenResponse("The provided penalty_per_compile_error ".$postData['penalty_per_compile_error']." is not permitted.");
		}

		$penalty_per_time_limit = trim($postData['pen_per_time']);
		if(!is_numeric($penalty_per_time_limit) || $penalty_per_time_limit < 0 || $penalty_per_time_limit != round($penalty_per_time_limit)){					
			return $this->returnForbiddenResponse("The provided penalty_per_time_limit ".$postData['penalty_per_time_limit']." is not permitted.");
		}

		$penalty_per_runtime_error = trim($postData['pen_per_runtime']);
		if(!is_numeric($penalty_per_runtime_error) || $penalty_per_runtime_error < 0 || $penalty_per_runtime_error != round($penalty_per_runtime_error)){					
			return $this->returnForbiddenResponse("The provided penalty_per_runtime_error ".$postData['penalty_per_runtime_error']." is not permitted.");
		}

		$practiceContest->penalty_per_wrong_answer = (int)$penalty_per_wrong_answer;	
		$practiceContest->penalty_per_compile_error = (int)$penalty_per_compile_error;
		$practiceContest->penalty_per_time_limit = (int)$penalty_per_time_limit;
		$practiceContest->penalty_per_runtime_error = (int)$penalty_per_runtime_error;

		$actualContest->penalty_per_wrong_answer = (int)$penalty_per_wrong_answer;	
		$actualContest->penalty_per_compile_error = (int)$penalty_per_compile_error;
		$actualContest->penalty_per_time_limit = (int)$penalty_per_time_limit;
		$actualContest->penalty_per_runtime_error = (int)$penalty_per_runtime_error;
		
		
		
		# TIMES
		
		// practice start
		$unix_practice_start = strtotime($postData['practice_start_date']);			
		if(!$unix_practice_start){
			return $this->returnForbiddenResponse("practice_start_date provided is not valid");
		}
		
		$practice_start_date = new DateTime();
		$practice_start_date->setTimestamp($unix_practice_start);
		
		// practice end
		$unix_practice_end = strtotime($postData['practice_end_date']);			
		if(!$unix_practice_end){
			return $this->returnForbiddenResponse("practice_start_date provided is not valid");
		}
		
		$practice_end_date = new DateTime();
		$practice_end_date->setTimestamp($unix_practice_end);
		
		// actual start
		$unix_actual_start = strtotime($postData['actual_start_date']);			
		if(!$unix_actual_start){
			return $this->returnForbiddenResponse("actual_start_date provided is not valid");
		}
		
		$actual_start_date = new DateTime();
		$actual_start_date->setTimestamp($unix_actual_start);
		
		// actual end
		$unix_actual_end = strtotime($postData['actual_end_date']);			
		if(!$unix_actual_end){
			return $this->returnForbiddenResponse("actual_end_date provided is not valid");
		}
		
		$actual_end_date = new DateTime();
		$actual_end_date->setTimestamp($unix_actual_end);
		
		// validate the times
		if($practice_start_date >= $practice_end_date){
			return $this->returnForbiddenResponse("Practice start time must be before end time");
		}
		
		if($actual_start_date >= $actual_end_date){
			return $this->returnForbiddenResponse("Actual start time must be before end time");
		}
		
		if($practice_end_date >= $actual_start_date){
			return $this->returnForbiddenResponse("Contest times overlap");			
		}
				
		
		// get the scoreboard freeze time
		$freezeHours = trim($postData['freeze_hours']);
		$freezeMins = trim($postData['freeze_minutes']);
		
		if(!is_numeric($freezeHours) || $freezeHours < 0 || $freezeHours != round($freezeHours)){
			return $this->returnForbiddenResponse("freeze_hours is not valid");
		}
		
		if(!is_numeric($freezeMins) || $freezeMins < 0 || $freezeMins != round($freezeMins)){
			return $this->returnForbiddenResponse("freeze_minutes is not valid");
		}
		
		$di = DateInterval::createFromDateString($freezeHours." hours + ".$freezeMins." minutes");
		
		$actual_freeze_date = clone $actual_end_date;
		$practice_freeze_date = clone $practice_end_date;
		
		# for now, don't allow freezing the scoreboard in the practice contest
		$actual_freeze_date->sub($di);
		//$practice_freeze_date->sub($di);
		
		if(!$actual_freeze_date || !$practice_freeze_date){
			return $this->returnForbiddenResponse("Freeze date is not valid ");
		}
		
		// set the freeze time to be the start time if the freeze time is extra long
		if($actual_freeze_date < $actual_start_date){
			$actual_freeze_date = clone $actual_start_date;
		}
		
		if($practice_freeze_date < $practice_start_date){
			$practice_freeze_date = clone $practice_start_date;
		}
		
		$practiceContest->start_time = $practice_start_date;
		$practiceContest->end_time = $practice_end_date;
		$practiceContest->cutoff_time = $practice_end_date;			
		$practiceContest->freeze_time = $practice_freeze_date;
		
		$actualContest->start_time = $actual_start_date;
		$actualContest->end_time = $actual_end_date;
		$actualContest->cutoff_time = $actual_end_date;		
		$actualContest->freeze_time = $actual_freeze_date;
		
		
		$section->start_time = clone $practice_start_date;
		$section->start_time->sub(new DateInterval('P1D'));
		
		$section->end_time = clone $actual_end_date;	
		$section->end_time->add(new DateInterval('P7D'));	
		
		# JUDGES
		$section->user_roles->clear();	
			
		$judges = json_decode($postData['judges']);
		$teams = json_decode($postData['teams']);
		
		$judgeRole = $em->getRepository("AppBundle\Entity\Role")->findOneBy([
			'role_name' => 'Judges',
		]);
		
		foreach($judges as $judge){			
			
			if(isset($judge->id) && isset($judge->name)){			
			
				if($judge->id == 0){
					
					// validate email
					if( !filter_var($judge->name, FILTER_VALIDATE_EMAIL) ) {
						return $this->returnForbiddenResponse("Email address ".$judge->name." is not valid");
					}
					
					$judgeUser = $em->getRepository('AppBundle\Entity\User')->findOneBy([
						'email' => $judge->name,
					]);
					
					if(!$judgeUser){
						$judgeUser = new User($judge->name, $judge->name);	
						$em->persist($judgeUser);
					}
					
				} else {
					
					$judgeUser = $em->find('AppBundle\Entity\User', $judge->id);
					
					if(!$judgeUser){
						return $this->returnForbiddenResponse("Unable to find user with id: ".$judge->id);
					}
				}
				
				$usr = new UserSectionRole($judgeUser, $section, $judgeRole);			
				$section->user_roles->add($usr);
				
			} else {
				
				return $this->returnForbiddenResponse("Judge not formatted properly");
				
			}			
		}
		
		$takeRole = $em->getRepository("AppBundle\Entity\Role")->findOneBy([
			'role_name' => 'Takes',
		]);		
		
		# TEAMS
		$newPracticeTeams = new ArrayCollection();
		$newActualTeams = new ArrayCollection();	
		
		foreach($teams as $team){
			
			if(isset($team->id) && count($team->id) == 2 && isset($team->name) && isset($team->members) && count($team->members) > 0){
				
				// decide if new teams need to be made
				if($team->id[0] != 0 && $team->id[1] != 0){
				
				
					$teamPractice = $em->find('AppBundle\Entity\Team', $team->id[0]);					
					if(!$teamPractice || $teamPractice->assignment != $practiceContest){
						return $this->returnForbiddenResponse("Unable to find team with id: ".$team->id[0]);
					}
					
					$teamActual = $em->find('AppBundle\Entity\Team', $team->id[1]);					
					if(!$teamActual || $teamActual->assignment != $actualContest){
						return $this->returnForbiddenResponse("Unable to find team with id: ".$team->id[1]);
					}
				
				
				} else {
						
					$teamPractice = new Team();
					$teamActual = new Team();	
				}
					
				$teamPractice->assignment = $practiceContest;
				$teamActual->assignment = $actualContest;
				
				# set names
				$teamPractice->name = $team->name;
				$teamActual->name = $team->name;
				
				$teamPractice->users->clear();
				$teamActual->users->clear();
				
				# members
				foreach($team->members as $member){
					
					if(isset($member->id) && isset($member->name)){
						
					
						if($member->id == 0){
					
							// validate email
							if( !filter_var($member->name, FILTER_VALIDATE_EMAIL) ) {
								return $this->returnForbiddenResponse("Email address ".$member->name." is not valid");
							}
							
							$teamUser = $em->getRepository('AppBundle\Entity\User')->findOneBy([
								'email' => $member->name,
							]);
							
							if(!$teamUser){
								$teamUser = new User($member->name, $member->name);	
								$em->persist($teamUser);
							}
							
						} else {
							
							$teamUser = $em->find('AppBundle\Entity\User', $member->id);
							
							if(!$teamUser){
								return $this->returnForbiddenResponse("Unable to find user with id: ".$member->id);
							}
						}
						
						$usr = new UserSectionRole($teamUser, $section, $takeRole);			
						$section->user_roles->add($usr);
						
						$teamPractice->users->add($teamUser);
						$teamActual->users->add($teamUser);
						
					} else {
						
						return $this->returnForbiddenResponse("Member not formatted properly");						
					}
				}
				
				
				$newPracticeTeams->add($teamPractice);
				$newActualTeams->add($teamActual);
				
			} else {
				
				return $this->returnForbiddenResponse("Team not formatted properly");				
			}
			
		}		
		
		if($practiceContest->teams){
			$practiceToRemove = clone $practiceContest->teams;
		} else {
			$practiceToRemove = new ArrayCollection();
		}
		
		
		if($actualContest->teams){			
			$actualToRemove = clone $actualContest->teams;
		} else {
			$actualToRemove = new ArrayCollection();			
		}
		
		# clear out and replace the teams 
		foreach($practiceContest->teams as &$team){
			$team->assignment = null;
		}
		foreach($actualContest->teams as &$team){
			$team->assignment = null;
		}		
		
		foreach($newPracticeTeams as &$team){
			
			$practiceToRemove->removeElement($team);
			
			$team->assignment = $practiceContest;
			$em->persist($team);
		}
		
		foreach($newActualTeams as &$team){
			
			$actualToRemove->removeElement($team);
			
			$team->assignment = $actualContest;	
			$em->persist($team);
		}
		
		foreach($practiceToRemove as &$team){
			$em->remove($team);
			$em->flush();
		}
		
		foreach($actualToRemove as &$team){
			$em->remove($team);
			$em->flush();
		}			
		
		
		$em->persist($section);
		$em->flush();			
		
		# CLEANUP 
		
		$url = $this->generateUrl('contest', ['contestId' => $section->id]);
				
		$response = new Response(json_encode([
			'id' => $section->id,
			'redirect_url' => $url,
			'section' => $section,
		]));			
		
		$response->headers->set('Content-Type', 'application/json');
		$response->setStatusCode(Response::HTTP_OK);

		return $response;	
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
		if(!$submission || $submission->problem != $problem || !$submission->is_completed){
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
			->andWhere('s.is_completed = ?3')
			->orderBy('s.timestamp', 'ASC')
			->setParameter(1, $contest->problems->toArray())
			->setParameter(2, 0)
			->setParameter(3, true);
		$subs_query = $qb_allsubs->getQuery();
		$pending_submissions = $subs_query->getResult();
				
		$qb_revsubs = $em->createQueryBuilder();
		$qb_revsubs->select('s')
			->from('AppBundle\Entity\Submission', 's')
			->where('s.problem IN (?1)')
			->andWhere('s.pending_status = ?2')
			->andWhere('s.is_completed = ?3')
			->orderBy('s.timestamp', 'ASC')
			->setParameter(1, $contest->problems->toArray())
			->setParameter(2, 2)
			->setParameter(3, true);
		$rev_subs_query = $qb_revsubs->getQuery();
		$reviewed_submissions = $rev_subs_query->getResult();
		
		# get user's claimed subs
		$qb_claimed = $em->createQueryBuilder();
		$qb_claimed->select('s')
			->from('AppBundle\Entity\Submission', 's')
			->where('s.problem IN (?1)')
			->andWhere('s.pending_status = ?2')
			->andWhere('s.reviewer = ?3')
			->andWhere('s.is_completed = ?4')
			->orderBy('s.timestamp', 'ASC')
			->setParameter(1, $contest->problems->toArray())
			->setParameter(2, 1)
			->setParameter(3, $user)
			->setParameter(4, true);
		$claim_query = $qb_claimed->getQuery();
		$claimed_submissions = $claim_query->getResult();
				
		// get the queries for the contest
		$qb_clars = $em->createQueryBuilder();
		$qb_clars->select('s')
			->from('AppBundle\Entity\Query', 's')
			->where('s.problem IN (?1)')
			->orWhere('s.assignment IN (?2)')
			->andWhere('s.answer IS NULL')
			->orderBy('s.timestamp', 'ASC')
			->setParameter(1, $contest->problems->toArray())
			->setParameter(2, $contest);
		$clar_query = $qb_clars->getQuery();
		$clarifications = $clar_query->getResult();	
		
		// get the answered queries for the contest
		$qb_ans = $em->createQueryBuilder();
		$qb_ans->select('s')
			->from('AppBundle\Entity\Query', 's')
			->where('s.problem IN (?1)')
			->orWhere('s.assignment IN (?2)')
			->andWhere('s.answer IS NOT NULL')
			->orderBy('s.timestamp', 'ASC')
			->setParameter(1, $contest->problems->toArray())
			->setParameter(2, $contest);
		$ans_query = $qb_ans->getQuery();
		$answered_clarifications = $ans_query->getResult();	
		
		$response = new Response(json_encode([
			'pending_submissions' => $pending_submissions,
			'reviewed_submissions' => $reviewed_submissions,
			'claimed_submissions' => $claimed_submissions,
			
			'clarifications' => $clarifications,
			'answered_clarifications' => $answered_clarifications,
		]));
			
		
		$response->headers->set('Content-Type', 'application/json');
		$response->setStatusCode(Response::HTTP_OK);

		return $response;
	}
	
	public function postQuestionAction(Request $request){
		
		$em = $this->getDoctrine()->getManager();		
		$grader = new Grader($em);

		$user = $this->get('security.token_storage')->getToken()->getUser();
		if(!$user){
			die("USER DOES NOT EXIST");
		}
		# see which fields were included	
		$postData = $request->request->all();
		
		$contest = $em->find('AppBundle\Entity\Assignment', $postData['contestId']);
		
		if(!$contest){
			return $this->returnForbiddenResponse("Contest ID provided was not valid.");
		}
		
		# validation
		if(!($user->hasRole("ROLE_SUPER") || $user->hasRole("ROLE_ADMIN") || $grader->isJudging($user, $contest->section) || $grader->getTeam($user, $contest))){
			return $this->returnForbiddenResponse("You are not allowed to edit this submission");
		}
		
		if(isset($postData['problemId'])){
			
			$problem = $em->find('AppBundle\Entity\Problem', $postData['problemId']);
			
			if(!$problem || $problem->assignment != $contest){
				return $this->returnForbiddenResponse("Problem ID provided was not valid.");
			}
		}
		
		if(!isset($postData['question']) || trim($postData['question']) == ""){
			return $this->returnForbiddenResponse('Question was not provided');
		}
		
		$query = new Query();
		
		if($problem){
			$query->problem = $problem;
		} else {
			$query->assignment = $contest;
		}
		
		$query->question = trim($postData['question']);
		$query->timestamp = new \DateTime("now");
		$query->asker = $grader->getTeam($user, $contest);
		
		$em->persist($query);
		$em->flush();
		
		$response = new Response(json_encode([
			'id' => $query->id, 
		]));		
					
		$response->headers->set('Content-Type', 'application/json');
		$response->setStatusCode(Response::HTTP_OK);

		return $response;		
	}
	
	public function submissionJudgingAction(Request $request){
		
		$em = $this->getDoctrine()->getManager();		
		$grader = new Grader($em);

		$user = $this->get('security.token_storage')->getToken()->getUser();
		if(!$user){
			die("USER DOES NOT EXIST");
		}
		# see which fields were included	
		$postData = $request->request->all();
		
		// for submission editing
		if(isset($postData['submissionId'])){
		
			$submission = $em->find('AppBundle\Entity\Submission', $postData['submissionId']);
		
			if(!$submission){
				return $this->returnForbiddenResponse('Submission ID is not valid.');
			}
			
			# validation
			if(!($user->hasRole("ROLE_SUPER") || $user->hasRole("ROLE_ADMIN") || $grader->isJudging($user, $submission->problem->assignment->section))){
				return $this->returnForbiddenResponse("You are not allowed to edit this submission");
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
			
			
			$em->flush();	
			
			$response = new Response(json_encode([
				'id' => $submission->id,
				'reviewed' => $reviewed, 
			]));
				
			
			$response->headers->set('Content-Type', 'application/json');
			$response->setStatusCode(Response::HTTP_OK);

			return $response;
			
		} 
		// for clarification editing
		else if(isset($postData['clarificationId'])){
			
			// Posting a notice
			if($postData['clarificationId'] == 0){
				$contest = $em->find('AppBundle\Entity\Assignment', $postData['contestId']);

				if(!$contest){
					return $this->returnForbiddenResponse('Contest ID is not valid.');
				}
				
				$section = $contest->section;
				
				$query = new Query();
				$em->persist($query);
				
				$query->assignment = $contest;
				$query->answerer = $user;
				$query->timestamp = new \DateTime('now');
			}
			// Answering a query
			else {

				$query = $em->find('AppBundle\Entity\Query', $postData['clarificationId']);
				
				if(!$query){
					return $this->returnForbiddenResponse('Clarification ID is not valid.');
				}
				
				$section = null;
				
				if($query->problem){
					$problem = $query->problem;
					$section = $problem->assignment->section;
				} else {
					$assignment = $query->assignment;
					$section = $assignment->section;
				}
				
				$query->answerer = $user;
				if($postData['global']){
					$query->asker = null;
				}
			}
			
			# validation
			if(!($user->hasRole("ROLE_SUPER") || $user->hasRole("ROLE_ADMIN") || $grader->isJudging($user, $section))){
				return $this->returnForbiddenResponse("You are not allowed to modify this query");
			}							
			
			$answer = $postData['answer'];
			
			// add answer to the query
			if(!isset($answer)){
				return $this->returnForbiddenResponse("Answer provided was not valid");
			}
			
			$query->answer = $answer;
						
			if(trim($answer) == ""){
				$qid = $query->id;
				$em->remove($query);
			}
			
			$em->flush();
			
			$response = new Response(json_encode([
				'id' => ($qid) ? $qid : $query->id,
				'answered' => $answered, 
			]));		
						
			$response->headers->set('Content-Type', 'application/json');
			$response->setStatusCode(Response::HTTP_OK);

			return $response;
						
		}
		// error
		else {
			return $this->returnForbiddenResponse("Submission or clarification ID not provided");
		}
	}
		
	private function returnForbiddenResponse($message){		
		$response = new Response($message);
		$response->setStatusCode(Response::HTTP_FORBIDDEN);
		return $response;
	}

	
	
	}

?>
