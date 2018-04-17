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
use AppBundle\Utils\SocketPusher;

use Doctrine\Common\Collections\ArrayCollection;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class ContestPostController extends Controller {

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
		
		
		$grader = new Grader($em);
		
		$elevatedUser = $user->hasRole("ROLE_SUPER") || $user->hasRole("ROLE_ADMIN") || $grader->isJudging($user, $assignment->section);		
		if( !($elevatedUser) ){
			return $this->returnForbiddenResponse("PERMISSION DENIED");
		}
		
				
		# PROBLEM
		if(isset($postData['problemId'])){
			
			if($postData['problemId'] == 0){

				$problem = new Problem();
				$problem->assignment = $assignment;

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
		$problem->stop_on_first_fail = false;
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

		foreach($assignment->contest_languages->toArray() as $lang){
			$pl = new ProblemLanguage();
			$pl->problem = $problem;
			$pl->language = $lang;

			$problem->problem_languages->add($pl);
		}
		
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

		// update the leaderboard
		$assignment->updateLeaderboard($grader, $em);
		
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
		
		$grader = new Grader($em);
		
		$elevatedUser = $user->hasRole("ROLE_SUPER") || $user->hasRole("ROLE_ADMIN");				
		
		$contests = [];

		# SECTION 		
		if(!isset($postData['contestId'])){
			
			return $this->returnForbiddenResponse("contestId not provided");
			
		} else if($postData['contestId'] > 0){
			
			$section = $em->find('AppBundle\Entity\Section', $postData['contestId']);
			if(!$section || $section->course != $course || !$section->course->is_contest){
				return $this->returnForbiddenResponse("Contest does not exist.");
			}
			
			
			$elevatedUser = $elevatedUser || $grader->isJudging($user, $section);
			
			if( !($elevatedUser) ){
				return $this->returnForbiddenResponse("PERMISSION DENIED");
			}
						
		} else {
			
			if( !($elevatedUser) ){
				return $this->returnForbiddenResponse("PERMISSION DENIED");
			}	

			$section = new Section();
			
			# set up the section
			$section->course = $course;
			$section->semester = "";
			$section->year = 0;
			$section->is_public = false;
			$section->is_deleted = false;					
		}


		# CONTESTS
		$contestsToRemove = [];
		
		foreach($section->assignments as $asgn){
			$contestsToRemove[$asgn->id] = $asgn;	
		};

		$section->assignments->clear();

		$postContests = (array) json_decode($postData['contests']);

		if(count($postContests) < 1){
			return $this->returnForbiddenResponse("Provided contests was empty");
		}

		foreach($postContests as $pc){

			if($pc->id){
			
				$contest = $em->find('AppBundle\Entity\Assignment', $pc->id);
				if(!$contest || $contest->section != $section){
					return $this->returnForbiddenResponse("Assignment does not exist.");
				}

			} else {

				$contest = new Assignment();
				$contest->section = $section;
			}

			$contest->name = $pc->name;
			$contest->description = "";
			$contest->weight = 1;
			$contest->is_extra_credit = false;
			$contest->penalty_per_day = 0;


			# TIMES		
			$unix_start = strtotime($pc->times[0]);			
			if(!$unix_start){
				return $this->returnForbiddenResponse("Provided start date is not valid");
			}			
			$start_date = new DateTime();
			$start_date->setTimestamp($unix_start);

			$unix_end = strtotime($pc->times[1]);			
			if(!$unix_end){
				return $this->returnForbiddenResponse("Provided end date is not valid");
			}			
			$end_date = new DateTime();
			$end_date->setTimestamp($unix_end);
			
			// validate the times
			if($start_date >= $end_date){
				return $this->returnForbiddenResponse("Provided times conflict with each other");
			}
						
			// build the scoreboard freeze time
			$freezeMins = trim($pc->min_freeze);
			$freezeHours = trim($pc->hour_freeze);
			
			if(!is_numeric($freezeHours) || $freezeHours < 0 || $freezeHours != round($freezeHours)){
				return $this->returnForbiddenResponse("Provided freeze hours is not valid");
			}
			
			if(!is_numeric($freezeMins) || $freezeMins < 0 || $freezeMins != round($freezeMins)){
				return $this->returnForbiddenResponse("Provided freeze minutes is not valid");
			}
			
			$di = DateInterval::createFromDateString($freezeHours." hours + ".$freezeMins." minutes");
			$freeze_date = clone $end_date;	
			$freeze_date->sub($di);
			
			if(!$freeze_date){
				return $this->returnForbiddenResponse("Calculated freeze date is not valid");
			}
			// set the freeze time to be the start time if the freeze time is extra long
			else if($freeze_date < $start_date){
				$freeze_date = clone $start_date;
			}
			
			$contest->start_time = $start_date;
			$contest->end_time = $end_date;
			$contest->cutoff_time = $end_date;			
			$contest->freeze_time = $freeze_date;

			$contests[] = $contest;
		}

		foreach($contests as &$cntst){
			unset($contestsToRemove[$cntst->id]);
			$section->assignments->add($cntst);
		}

		$section->start_time = clone $contests[0]->start_time;
		$section->start_time->sub(new DateInterval('P30D'));
		
		$section->end_time = clone $contests[count($contests)-1]->end_time;	
		$section->end_time->add(new DateInterval('P14D'));

		// LANGUAGES
		$languages = json_decode($postData['languages']);
		if(count($languages) < 1){
			return $this->returnForbiddenResponse("At least one language must be provided.");
		}

		foreach($contests as &$cntst){
			$cntst->contest_languages->clear();
		}

		foreach($languages as $language_id){

			if(!isset($language_id)){
				return $this->returnForbiddenResponse("Language id must be provided");
			}

			$language = $em->find('AppBundle\Entity\Language', $language_id);

			if(!$language){
				return $this->returnForbiddenResponse("Could not find language with id: ".$language_id);
			}
			
			foreach($contests as &$cntst){
				$cntst->contest_languages->add($language);
			}
		}

		// reset the languages for all of the problems that already exist
		foreach($contests as &$cntst){
			foreach($cntst->problems as &$prob){
				$prob->problem_languages->clear();
	
				foreach($cntst->contest_languages as $lang){
					$pl = new ProblemLanguage();
					$pl->problem = $prob;
					$pl->language = $lang;
	
					$prob->problem_languages->add($pl);
				}
	
				$em->persist($prob);
			}
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

		foreach($contests as &$cntst){
			$cntst->penalty_per_wrong_answer = (int)$penalty_per_wrong_answer;	
			$cntst->penalty_per_compile_error = (int)$penalty_per_compile_error;
			$cntst->penalty_per_time_limit = (int)$penalty_per_time_limit;
			$cntst->penalty_per_runtime_error = (int)$penalty_per_runtime_error;
		}			
		
		# JUDGES
		$section->user_roles->clear();	
			
		$judges = json_decode($postData['judges']);
		
		$judgeRole = $em->getRepository("AppBundle\Entity\Role")->findOneBy([
			'role_name' => 'Judges',
		]);
		
		foreach($judges as $judge){			
			
			if(isset($judge->id) && isset($judge->name)){			
			
				if($judge->id == 0){
					
					// validate email
					if( !filter_var($judge->name, FILTER_VALIDATE_EMAIL) ) {
						
						$judge->name = $judge->name."@cedarville.edu";
						
						if( !filter_var($judge->name, FILTER_VALIDATE_EMAIL) ){
							return $this->returnForbiddenResponse("Email address ".$judge->name." is not valid");	
						}
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
		
		# TEAMS		
		$takeRole = $em->getRepository("AppBundle\Entity\Role")->findOneBy([
			'role_name' => 'Takes',
		]);	

		$teams = json_decode($postData['teams']);			
		
		$allMembers = [];

		$newTeams = [];	
		foreach($contests as $ct){
			$newTeams[] = new ArrayCollection();
		}

		# TEAM CREATION
		foreach($teams as $team){
							
			if( !(isset($team->id) && isset($team->name) && isset($team->members)) ){
				return $this->returnForbiddenResponse("Team data was not formatted properly");
			}

			if(count($team->id) != count($contests)){
				return $this->returnForbiddenResponse("Team does not have enough ids");
			}

			if(count($team->members) < 1){
				return $this->returnForbiddenResponse("Team does not have enough members");
			}

			# GET AN ARRAY OF ALL MEMBERS
			$members = [];
			foreach($team->members as $member){

				if(! (isset($member->id) && isset($member->name)) ){
					return $this->returnForbiddenResponse("Member not formatted properly");	
				}
			
				if($member->id == 0){
			
					// validate email

					if( !filter_var($member->name, FILTER_VALIDATE_EMAIL) ) {								
				
						$member->name = $member->name."@cedarville.edu";
						
						if( !filter_var($member->name, FILTER_VALIDATE_EMAIL) ) {
							return $this->returnForbiddenResponse("Email address ".$member->name." is not valid");
						}
					}
					
					$teamUser = $em->getRepository('AppBundle\Entity\User')->findOneBy([
						'email' => $member->name,
					]);

					if($teamUser == null){
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

				if($allMembers[$teamUser->getEmail()]){
					return $this->returnForbiddenResponse("User ".$teamUser->getEmail()." cannot be on two teams");
				}

				$allMembers[$teamUser->getEmail()] = $teamUser;

				$members[] = $teamUser;
			}

			# LOOP THROUGH EACH CONTEST AND ASSIGN MEMBERS
			$count = 0;
			foreach($team->id as $id){

				if($id == 0){
					
					$tm = new Team();

				} else {
				
					$tm = $em->find('AppBundle\Entity\Team', $id);

					if(!$tm || $tm->assignment != $contests[$count]){
						return $this->returnForbiddenResponse("Unable to find team with id: ".$id);
					}
				}

				$tm->assignment = $contests[$count];
				$tm->name = $team->name;
				$tm->workstation_number = $team->workstation_number;
				$tm->users->clear();
				
				foreach($members as &$member){
					$tm->users->add($member);
				}

				$newTeams[$count]->add($tm);

				$count++;
			}			
		}

		# POST-CONTEST CREATION
		$lastEndDate = clone $contests[count($contests)-1]->end_time;
		$firstStartDate = clone $contests[0]->start_time;
		$firstEndDate = clone $contests[0]->end_time;

		if(isset($postData['post_contest'])){
			
			$currTime = new \DateTime("now");
			
			if($postData['post_contest'] == 0 || $currTime <= $lastEndDate){

				$post_contest = new Assignment();
				$post_contest->section = $section;		

			} else {
				$post_contest = $em->find('AppBundle\Entity\Assignment', intval($postData['post_contest']));
				if(!$post_contest || $post_contest->section != $section){
					return $this->returnForbiddenResponse("Post-contest assignment does not exist.");
				}
			}
			

			$post_contest->post_contest = true;
			$post_contest->name = "Post-Contest";
			$post_contest->description = "";
			$post_contest->weight = 1;
			$post_contest->is_extra_credit = false;
			$post_contest->penalty_per_day = 0;

			$post_contest->start_time = clone $lastEndDate;			
			$post_contest->start_time->add(new DateInterval('P0DT1H'));			
			$post_contest->end_time = clone $lastEndDate;
			$post_contest->end_time->add(new DateInterval('P180D'));
			$post_contest->cutoff_time = clone $lastEndDate;
			$post_contest->cutoff_time->add(new DateInterval('P180D'));
			$post_contest->freeze_time = clone $lastEndDate;
			$post_contest->freeze_time->add(new DateInterval('P180D'));

			unset($contestsToRemove[$post_contest->id]);
			$em->persist($post_contest);	
		}

		# PRE-CONTEST CREATION
		if(isset($postData['pre_contest'])){

			if($postData['pre_contest'] == 0){

				$pre_contest = new Assignment();
				$pre_contest->section = $section;		

			} else {
				$pre_contest = $em->find('AppBundle\Entity\Assignment', intval($postData['pre_contest']));
				if(!$pre_contest || $pre_contest->section != $section){
					return $this->returnForbiddenResponse("Pre-contest assignment does not exist.");
				}
			}
			
			$pre_contest->pre_contest = true;
			$pre_contest->name = "Pre-Contest";
			$pre_contest->description = "";
			$pre_contest->weight = 1;
			$pre_contest->is_extra_credit = false;
			$pre_contest->penalty_per_day = 0;

			$pre_contest->start_time = clone $firstStartDate;	
			$pre_contest->start_time->sub(new DateInterval('P7D'));		
			$pre_contest->end_time = clone $firstEndDate;
			$pre_contest->end_time->sub(new DateInterval('P0DT1H'));
			$pre_contest->cutoff_time = clone $firstEndDate;
			$pre_contest->cutoff_time->sub(new DateInterval('P0DT1H'));
			$pre_contest->freeze_time = clone $firstEndDate;
			$pre_contest->freeze_time->sub(new DateInterval('P0DT1H'));
			

			# PRE CONTEST LANGUAGES
			$pre_contest->contest_languages->clear();
			
			foreach($languages as $language_id){
				$language = $em->find('AppBundle\Entity\Language', $language_id);
				$pre_contest->contest_languages->add($language);
			}
	
			// reset the languages for all of the problems that already exist
			foreach($pre_contest->problems as &$prob){
				$prob->problem_languages->clear();
	
				foreach($pre_contest->contest_languages as $lang){
					$pl = new ProblemLanguage();
					$pl->problem = $prob;
					$pl->language = $lang;
	
					$prob->problem_languages->add($pl);
				}
	
				$em->persist($prob);
			}


			$toRemove = $pre_contest->teams->toArray();
			$pre_contest->teams->clear();

			foreach($allMembers as $email => $user){
				$tm = new Team();

				$tm->assignment = $pre_contest;
				$tm->name = $user->getFullName();
				$tm->workstation_number = 0;
				
				$tm->users->add($user);
				
				$pre_contest->teams->add($tm);
			}

			foreach($toRemove as &$team){
				$em->remove($team);
				$em->flush();
			}

			unset($contestsToRemove[$pre_contest->id]);
			$em->persist($pre_contest);	
		}
		
		
		# DELETE OLD TEAMS AND CREATE (PERSIST) NEW ONES
		$count = 0;
		foreach($contests as &$cntst){
			
			if($cntst->teams){
				$toRemove = clone $cntst->teams;
			} else {
				$toRemove = new ArrayCollection();
			}


			foreach($cntst->teams as &$team){
				$team->assignment = null;
			}

			foreach($newTeams[$count] as &$team){
				$toRemove->removeElement($team);

				$team->assignment = $cntst;
				$em->persist($team);
			}

			foreach($toRemove as &$team){
				$em->remove($team);
				$em->flush();
			}

			$count++;
		}
		foreach($contestsToRemove as &$cntst){
			$em->remove($cntst);
			$em->flush();
		}

		$em->persist($section);
		$em->flush();			

		foreach($section->assignments as &$asgn){
			$asgn->updateLeaderboard($grader, $em);
		}
		
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
	
	// this is called when a user wants to ask a question
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
		
		$section = $contest->section;
		
		# validation
		$grader = new Grader($em);
		$elevatedUser = $grader->isJudging($user, $section) || $user->hasRole("ROLE_SUPER") || $user->hasRole("ROLE_ADMIN");

		if( !($elevatedUser || ($grader->isTaking($user, $section) && $section->isActive())) ){
			return $this->returnForbiddenResponse("PERMISSION DENIED");
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

		# SOCKET PUSHER
		$pusher = new SocketPusher($this->container->get('gos_web_socket.wamp.pusher'), $em, $contest);
		$pusher->sendNewClarification($query);

		return $response;		
	}
		
	public function scoreboardFreezeAction(Request $request){
		
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
		
		$section = $contest->section;
		
		# validation
		$elevatedUser = $user->hasRole("ROLE_SUPER") || $user->hasRole("ROLE_ADMIN") || $grader->isJudging($user, $section);
		if( !($elevatedUser) ){
			return $this->returnForbiddenResponse("You are not allowed to modify the scoreboard");
		}
		
		# get the type 
		if( !isset($postData['type']) ){
			return $this->returnForbiddenResponse('type was not provided');
		}

		$currTime = new \DateTime("now");	
		$frozen = ($contest->freeze_time <= $currTime);
			
		if($postData['type'] == "freeze"){
			
			// scoreboard is naturally open
			if(!$frozen){
				
				// scoreboard is frozen at this moment so only submissions at this moment and before can be seen
				$contest->freeze_override_time = $currTime;
				$contest->freeze_override = true;
								
			}
			// scoreboard is already overriden, undo the changes
			else if($contest->freeze_override && $contest->freeze_override_time == null) {
			
				$contest->freeze_override_time = null;
				$contest->freeze_override = false;
				
			}
			// error
			else {
				return $this->returnForbiddenResponse("Scoreboard is already frozen");				
			}
			
			$shouldFreeze = true;

		} else if($postData['type'] == "unfreeze"){
			
			// scoreboard is naturally frozen
			if($frozen){
				
				// scoreboard is unfrozen so all submissions can be seen
				$contest->freeze_override_time = null;
				$contest->freeze_override = true;
				
			} 
			// scoreboard is already overriden, undo the changes
			else if($contest->freeze_override && $contest->freeze_override_time != null) { 
			
				$contest->freeze_override_time = null;
				$contest->freeze_override = false;
				
			} 
			// error
			else {
				return $this->returnForbiddenResponse("Scoreboard is already unfrozen");
			}
			
			$shouldFreeze = false;
			
		} else {
			return $this->returnForbiddenResponse("type provided ".$postData['type']." is not valid");
		}
				
		$em->persist($contest);
		$em->flush();

		$response = new Response(json_encode([
			'id' => $contest->id,
			'freeze' => $shouldFreeze,
		]));		
					
		$response->headers->set('Content-Type', 'application/json');
		$response->setStatusCode(Response::HTTP_OK);

		// UPDATE LEADERBOARD
		$contest->updateLeaderboard($grader, $em);

		# SOCKET PUSHER
		$pusher = new SocketPusher($this->container->get('gos_web_socket.wamp.pusher'), $em, $contest);

		if($shouldFreeze){
			$pusher->sendFreeze();
		} else {
			$pusher->sendUnfreeze();
		}
		$pusher->sendScoreboardUpdates();

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
		
		if(!isset($postData['contestId'])){
			return $this->returnForbiddenResponse('contestId not provided');
		}
		
		$contest = $em->find('AppBundle\Entity\Assignment', $postData['contestId']);
		
		if(!$contest){
			return $this->returnForbiddenResponse('CONTEST DOES NOT EXIST');
		}
		
		$section = $contest->section;		
		
		$elevatedUser = $user->hasRole("ROLE_SUPER") || $user->hasRole("ROLE_ADMIN") || $grader->isJudging($user, $section);		
		if( !($elevatedUser) ){
			return $this->returnForbiddenResponse("PERMISSION DENIED");
		}

		# SOCKET PUSHER
		$pusher = new SocketPusher($this->container->get('gos_web_socket.wamp.pusher'), $em, $contest);
		
		
		// for submission editing
		if(isset($postData['submissionId'])){
		
			$submission = $em->find('AppBundle\Entity\Submission', $postData['submissionId']);
		
			if(!$submission){
				return $this->returnForbiddenResponse('Submission ID is not valid.');
			}
			
			# validation
			if($submission->problem->assignment != $contest){
				return $this->returnForbiddenResponse("PERMISSION DENIED");
			}			
			
			// check to make sure the submission hasn't been claimed
			// ************************* RACE CONDITIONS *************************
			if($submission->pending_status > 1 && !$postData['override']){
				return $this->returnForbiddenResponse("Submission has already been reviewed");
			}
			
			$update = true;
			$override_wrong = false;
			$reviewed = true;

			// saying the submission was incorrect
			if($postData['type'] == "wrong"){

				$override_wrong = true;
				
				
			}
			// saying the submission was correct
			else if($postData['type'] == "correct"){
				
				// override the submission to correct
				if($submission->isCorrect(true)){
					
					$submission->wrong_override = false;
					$submission->correct_override = false;	
					
				} else {
					
					$submission->wrong_override = false;
					$submission->correct_override = true;					
				}

				//$pusher->sendAcceptance($submission);

				
			} 
			// saying the submission was deleted
			else if($postData['type'] == "delete"){
					
				// delete the submission
				$subId = $submission->id;
				$em->remove($submission);

				//$pusher->sendDelete($submission);
					
			}
			// saying the submisison was a formatting error
			else if($postData['type'] == "formatting"){
				
				$override_wrong = true;
						
				// add formatting message to submission
				$submission->judge_message = "Formatting Error";
						
			}
			// saying the submission was a custom judge message error
			else if($postData['type'] == "message"){
				
				$override_wrong = true;
				$message = $postData['message'];
				
				// add custom message to submission
				if(!isset($message) || trim($message) == ""){
					$submission->judge_message = NULL;
				} else {
					$submission->judge_message = trim($postData['message']);
				}	
							
			}
			// claiming the submission
			else if($postData['type'] == "claimed"){
				
				$reviewed = false;			
				
				if($submission->pending_status > 0){
					return $this->returnForbiddenResponse("Submission has already been claimed");
				}	
				
				$submission->pending_status = 1;

				$update = false;

				// let the judges know this one has been claimed
				$pusher->sendClaimedSubmission($submission->id);
				
			}
			// unclaiming the submission
			else if($postData['type'] == "unclaimed"){
				
				$reviewed = false;			
				
				if($submission->pending_status < 1){
					return $this->returnForbiddenResponse("Submission has already been un-claimed");
				}	
				
				$submission->pending_status = 0;

				$update = false;	
				
				// let the other judges know this submission is back on the market
				$pusher->sendNewSubmission($submission);
				
			} else {
				return $this->returnForbiddenResponse("Type of judging command not allowed");
			}

			// do this if you need to override the submission to be wrong
			// (since it is used in many of the cases above)
			if($override_wrong){

				// override the submission to wrong
				if($submission->isCorrect(true) || $submission->isError()){
					
					$submission->wrong_override = true;				
					$submission->correct_override = false;
				} else {
					
					$submission->wrong_override = false;
					$submission->correct_override = false;
				}

				// let the team know their submission was rejected
				//$pusher->sendRejection($submission);
			}

			
			if($reviewed){
				$submission->pending_status = 2;
			}
			
			$submission->reviewer = $user;
			
			$submission->edited_timestamp = new \DateTime("now");
			
			
			$em->flush();	
			
			$response = new Response(json_encode([
				'id' => ($submission) ? $submission->id : $subId,
				'reviewed' => $reviewed, 
			]));
				
			
			$response->headers->set('Content-Type', 'application/json');
			$response->setStatusCode(Response::HTTP_OK);

			if($update){
				// UPDATE LEADERBOARD            	
				$contest->updateLeaderboard($grader, $em);

				if($postData['type'] != "delete"){
					$pusher->sendGradedSubmission($submission);
					$pusher->sendScoreboardUpdates();
				}				

				if($postData['type'] == "delete"){
					$type = "delete";
				} 
				else if($postData['type'] != "correct"){
					$type = "reject";
				} 
				else {
					$type = "accept";
				}

				$pusher->sendResponse($submission, $type);
			}
			
			return $response;
			
		} 
		// for clarification editing
		else if(isset($postData['clarificationId'])){
			
			// Posting a notice
			if($postData['clarificationId'] == 0){
				
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
				
				if( !((isset($query->problem) && $query->problem->assignment == $contest) || (isset($query->assignment) && $query->assignment == $contest)) ){
					return $this->returnForbiddenResponse('PERMISSION DENIED');
				}
			
				$query->answerer = $user;
				if($postData['global']){
					$query->asker = null;
				}
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

			# push a clarification message
			$pusher->sendClarification($query);

			return $response;
						
		}
		// for removing all submissions
		else if($postData['type'] == 'clear-subs'){
			
			if($contest->isActive()){
				return $this->returnForbiddenResponse("Cannot do this while the contest is running.");
			}
			
			$qb = $em->createQueryBuilder();
			$qb->delete('AppBundle\Entity\Submission', 's')
				->where('s.problem IN (?1)')
				->setParameter(1, $contest->problems->toArray());
			
			$query = $qb->getQuery();
			$res = $query->getResult();
			
			$response = new Response(json_encode([
				'good' => true,
			]));
				
			
			$contest->updateLeaderboard($grader, $em);

			$response->headers->set('Content-Type', 'application/json');
			$response->setStatusCode(Response::HTTP_OK);

			return $response; 		
		}
		// for removing all clarifications
		else if($postData['type'] == 'clear-clars'){
			
			if($contest->isActive()){
				return $this->returnForbiddenResponse("Cannot do this while the contest is running.");
			}
			
			
			$qb = $em->createQueryBuilder();
			$qb->delete('AppBundle\Entity\Query', 'q')
				->where('q.problem IN (?1)')
				->orWhere('q.assignment = (?2)')
				->setParameter(1, $contest->problems->toArray())
				->setParameter(2, $contest);
			
			$query = $qb->getQuery();
			$res = $query->getResult();
			
			$response = new Response(json_encode([
				'good' => true,
			]));

			$contest->updateLeaderboard($grader, $em);
						
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
