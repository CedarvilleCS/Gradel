<?php

namespace AppBundle\Utils;

use AppBundle\Entity\Role;
use AppBundle\Entity\User;
use AppBundle\Entity\Team;
use AppBundle\Entity\Course;
use AppBundle\Entity\Section;
use AppBundle\Entity\Assignment;
use AppBundle\Entity\Problem;
use AppBundle\Entity\ProblemLanguage;
use AppBundle\Entity\UserSectionRole;
use AppBundle\Entity\Testcase;
use AppBundle\Entity\Submission;
use AppBundle\Entity\Language;
use AppBundle\Entity\Feedback;
use AppBundle\Entity\TestcaseResult;

use \DateTime;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class Grader  {
	
	public $em;
	
	public function __construct($em) {
		
		if(get_class($em) != "Doctrine\ORM\EntityManager"){
			throw new Exception('The Grader class must be given a Doctrine\ORM\EntityManager but was given '.get_class($em));
		}
		
		$this->em = $em;		
	}
	
	
	private function isRole($user, $section, $role){

		$qb = $this->em->createQueryBuilder();
		$qb->select('usr')
			->from('AppBundle\Entity\UserSectionRole', 'usr')
			->where('usr.role = ?1')
			->andWhere('usr.user = ?2')
			->andWhere('usr.section = ?3')
			->setParameter(1, $role)
			->setParameter(2, $user)
			->setParameter(3, $section);
			
		$query = $qb->getQuery();
		$usr = $query->getOneOrNullResult();
		
		return isset($usr);
	}	
	
	public function isTeaching($user, $section){
		
		$role = $this->em->getRepository('AppBundle\Entity\Role')->findOneBy(array('role_name' => 'Teaches'));		
		
		return $this->isRole($user, $section, $role);
	}
	
	public function isTaking($user, $section){
		
		$role = $this->em->getRepository('AppBundle\Entity\Role')->findOneBy(array('role_name' => 'Takes'));		
		
		return $this->isRole($user, $section, $role);		
	}
		
	public function isHelping($user, $section){
		
		$role = $this->em->getRepository('AppBundle\Entity\Role')->findOneBy(array('role_name' => 'Helps'));		
		
		return $this->isRole($user, $section, $role);	
	}
	
	public function isJudging($user, $section){
		
		$role = $this->em->getRepository('AppBundle\Entity\Role')->findOneBy(array('role_name' => 'Judges'));		
		
		return $this->isRole($user, $section, $role);		
	}
	
		
	public function isOnTeam($user, $assignment, $team){
		return $team == $this->getTeam($user, $assignment);
	}
	
	public function getTeam($user, $assignment){
		
		# get all of the teams
		$qb_teams = $this->em->createQueryBuilder();
		$qb_teams->select('t')
				->from('AppBundle\Entity\Team', 't')
				->where('t.assignment = ?1')
				->setParameter(1, $assignment);
				
		$query_team = $qb_teams->getQuery();
		$team_entities = $query_team->getResult();
		
		# loop over all the teams for this assignment and figure out which team the user is a part of
		$team = null;
		
		foreach($team_entities as $tm) {
			foreach($tm->users as $us) {
				if($user->id == $us->id) {
					$team = $tm;
				}
			}
		}
		return $team;
	}
	
	public function getNumTotalAttempts($user, $problem){
		
		# get team from user
		$team = $this->getTeam($user, $problem->assignment);		
		
		# array of all submissions
		$qb_subs = $this->em->createQueryBuilder();
		$qb_subs->select('s')
			->from('AppBundle\Entity\Submission', 's')
			->where('s.problem = ?1')
			->andWhere('s.team = ?2')
			->setParameter(1, $problem)
			->setParameter(2, $team)
			->orderBy('s.timestamp', 'ASC');
			
		$subs_query = $qb_subs->getQuery();
		$subs = $subs_query->getResult();
				
		return count($subs);
	}
	
	public function getProbTotalAttempts($problem){	
		
		# array of all submissions
		$qb_subs = $this->em->createQueryBuilder();
		$qb_subs->select('s')
			->from('AppBundle\Entity\Submission', 's')
			->where('s.problem = ?1')
			->setParameter(1, $problem)
			->orderBy('s.timestamp', 'ASC');
			
		$subs_query = $qb_subs->getQuery();
		$subs = $subs_query->getResult();
				
		return count($subs);
	}
	
	public function getNumAttempts($user, $problem){
		
		# get team from user
		$team = $this->getTeam($user, $problem->assignment);		
		
		# accepted submission
		$qb_accepted_sub = $this->em->createQueryBuilder();
		$qb_accepted_sub->select('s')
			->from('AppBundle\Entity\Submission', 's')
			->where('s.problem = ?1')
			->andWhere('s.team = ?2')
			->andWhere('s.is_accepted = true')
			->setParameter(1, $problem)
			->setParameter(2, $team);
			
		$accepted_sub_query = $qb_accepted_sub->getQuery();
		$accepted_sub = $accepted_sub_query->getOneOrNullResult();

		if($accepted_sub == null){
			return 0;
		}
		
		# array of all submissions
		$qb_subs = $this->em->createQueryBuilder();
		$qb_subs->select('s')
			->from('AppBundle\Entity\Submission', 's')
			->where('s.problem = ?1')
			->andWhere('s.team = ?2')
			->setParameter(1, $problem)
			->setParameter(2, $team)
			->orderBy('s.timestamp', 'ASC');
			
		$subs_query = $qb_subs->getQuery();
		$subs = $subs_query->getResult();
		
		$attempts = 0;
		foreach($subs as $sub){
			
			if(!$sub->compiler_error){
				$attempts++;
			} 
			
			if($sub->percentage == 1){
				break;
			}
		}
		
		return $attempts;
	}
	
	public function getProblemGrade($user, $problem){
		
		$grades = [];

		$team = $this->getTeam($user, $problem->assignment);
		
		# accepted submission
		$qb_accepted_sub = $this->em->createQueryBuilder();
		$qb_accepted_sub->select('s')
			->from('AppBundle\Entity\Submission', 's')
			->where('s.problem = ?1')
			->andWhere('s.team = ?2')
			->andWhere('s.is_accepted = true')
			->setParameter(1, $problem)
			->setParameter(2, $team);
			
		$accepted_sub_query = $qb_accepted_sub->getQuery();
		$accepted_sub = $accepted_sub_query->getOneOrNullResult();
		
		$grades['accepted_submission'] = $accepted_sub;
		
		# test cases total
		$total_testcases = count($problem->testcases);
		
		$total_normal_testcases = 0;
		foreach($problem->testcases as $tc){
			
			if($tc->is_extra_credit){
				continue;
			}
			
			$total_normal_testcases++;			
		}
		
		$grades['total_testcases'] = $total_normal_testcases;	

		$grades['total_extra_testcases'] = $total_testcases - $total_normal_testcases;
		$attempts = $this->getNumAttempts($user, $problem);				
		$grades['attempts'] = $attempts;		
		
		# temp values
		$grades['passed_testcases'] = 0;
		$grades['percentage_raw'] = 0;
		$grades['percentage_adj'] = 0;
		
		if($accepted_sub){
			
			# test cases passed
			$passed_testcases = 0;
			$passed_extra_testcases = 0;
			foreach($accepted_sub->testcaseresults as $tcr){
				if($tcr->is_correct){
					if($tcr->testcase->is_extra_credit){
						$passed_extra_testcases++;
					} else {
						$passed_testcases++;
					}
				}
			}
			$grades['passed_testcases'] = $passed_testcases;
			$grades['passed_extra_testcases'] = $passed_extra_testcases;
			
			# percentage grade - raw
			$grades['percentage_raw'] = (float)$accepted_sub->percentage;
			
			# percentage grade - after mods			
			$num_before_penalty = $problem->attempts_before_penalty;
			$penalty_percentage = $problem->penalty_per_attempt;
			
			$attempts_to_penalize = max($attempts - $num_before_penalty, 0);
			$total_penalty = $attempts_to_penalize*$penalty_percentage;
			
			$adjusted_percentage = max($accepted_sub->percentage - $total_penalty, 0);
			$grades['percentage_adj'] = $adjusted_percentage;
		}
		
		return $grades;		
	}
	
	public function getAllProblemGrades($user, $assignment){
		
		$problems = [];
		
		# loop over all of the problem grades
		foreach($assignment->problems as $problem){
			
			$problems[$problem->id] = $this->getProblemGrade($user, $problem);
		}

		return $problems;		
	}
	
	public function getAssignmentGrade($user, $assignment){
		
		$grade = [];
	
		# get what kind of problems they are based on extra credit
		$normal_problem_count = 0;
		$extra_problem_count = 0;
		
		# get the total weight of problems
		$total_problem_weight = 0;		
		foreach($assignment->problems as $problem){
			
			if($problem->is_extra_credit){
				$extra_problem_count++;
			} else{
				$normal_problem_count++;
				$total_problem_weight+= $problem->weight;
			}			
		}	
		
		$total_problem_weight = max(1, $total_problem_weight);
	
		$num_correct_problems = 0;
		$num_extra_correct_problems = 0;
		
		$grade['num_problems'] = $normal_problem_count;
		$grade['num_extra_problems'] = $extra_problem_count;
		$grade['num_correct_problems'] = 0;
		$grade['num_extra_correct_problems'] = 0;
		
		$grade['percentage_raw'] = 0.0;
		$grade['percentage_adj'] = 0.0;
		
		$problem_grades = $this->getAllProblemGrades($user, $assignment);
		$grade['problem_grades'] = $problem_grades;
		
		
		$assignment_percentage = 0.0;
		foreach($assignment->problems as $problem){	
		
			$problem_percentage = $problem_grades[$problem->id]['percentage_adj'];
			
			$assignment_percentage += $problem_percentage*$problem->weight/$total_problem_weight;
						
			if($problem_grades[$problem->id]['total_testcases'] > 0 && $problem_grades[$problem->id]['passed_testcases'] == $problem_grades[$problem->id]['total_testcases']){
				
				if($problem->is_extra_credit){
					$num_extra_correct_problems++;
				} else {
					$num_correct_problems++;
				}
			}
		}
		
		$grade['num_correct_problems'] = $num_correct_problems;
		$grade['num_extra_correct_problems'] = $num_extra_correct_problems;
	
		$grade['percentage_raw'] = $assignment_percentage;
	
		$most_recent_sub = null;
		foreach($problem_grades as $pg){
			
			if(!$most_recent_sub || $most_recent_sub < $pg['accepted_submission']->timestamp){
				$most_recent_sub = $pg['accepted_submission']->timestamp;
			}
		}
	
		$num_days_over = 0;
		if($most_recent_sub > $assignment->end_time){
			$num_days_over = 1+(int)$most_recent_sub->diff($assignment->end_time)->format('%a');
		}
	
		$assignment_percentage_adj = max($assignment_percentage - $num_days_over*$assignment->penalty_per_day, 0);
		$grade['percentage_adj'] = $assignment_percentage_adj;

		return $grade;
	}
	
	public function getAllAssignmentGrades($user, $section){
		
		$assignments = [];
		
		# loop over all of the problem grades
		foreach($section->assignments as $assignment){
			
			$assignments[$assignment->id] = $this->getAssignmentGrade($user, $assignment);
		}

		return $assignments;
	}
		
	public function getSectionGrade($user, $section){
		
		$grade = [];
		
		$grade['percentage_adj'] = 0;
		
		$num_finished_assignments = 0;
		$num_future_assignments = 0;
		$num_finished_extra_assignments = 0;
		$num_future_extra_assignments = 0;
		
		$num_finished_assignments_noweight = 0;
		$num_future_assignments_noweight = 0;
		
		$total_finished_weight = 0;
		$total_weight = 0;
		
		$curr_time = new DateTime();
		
		$total_finished_weight = 0;
		foreach($section->assignments as $assignment){
			
			if($curr_time < $assignment->end_time || $assignment->is_extra_credit){
				continue;
			}
			
			$total_finished_weight += $assignment->weight;			
		}
		
		$total_finished_weight = max(1, $total_finished_weight);
		
		$assignment_grades = $this->getAllAssignmentGrades($user, $section);
		
		foreach($section->assignments as $assignment){
			
			if($curr_time < $assignment->end_time){
				continue;
			}
						
			$assignment_percentage = $assignment_grades[$assignment->id]['percentage_adj'];
			
			$total_percentage_weighted += $assignment_percentage*$assignment->weight/$total_finished_weight;
		}
		
		$grade['percentage_adj'] = $total_percentage_weighted;

		return $grade;
	}
	
	public function getAllSectionGrades($user){
			
		$qb_usr = $this->em->createQueryBuilder();
		$qb_usr->select('usr')
			->from('AppBundle\Entity\UserSectionRole', 'usr')
			->where('usr.user = ?1')
			->setParameter(1, $user);

		$usr_query = $qb_usr->getQuery();
		$usersectionroles = $usr_query->getResult();
		
		$sections = [];
		foreach($usersectionroles as $usr){
			
			if($usr->role->role_name == 'Takes'){
				$sections[] = $usr->section;	
			}			
		}
			
			
		$grades = [];
		foreach($sections as $section){
			$grades[$section->id] = $this->getSectionGrade($user, $section);
		}
		
		return $grades;
	}

	public function getFeedback($submission){
		
		if($submission->compiler_error){
			return null;
		}
		
		$problem = $submission->problem;
		
		$response_level = $problem->response_level;
		$display_testcaseresults = $problem->display_testcaseresults;
		$testcase_output_level = $problem->testcase_output_level;
		$extra_testcases_display = $problem->extra_testcases_display;		
		$stop_on_first_fail = $problem->stop_on_first_fail;
		
		$feedback = [];	
		
		// boolean for displaying individual testcase results
		$feedback['display_markers'] = $display_testcaseresults;
		// boolean for displaying individual extra credit testcase results
		$feedback['extra_testcases_display'] = $extra_testcases_display;
		// boolean for stopping on the first failure
		$feedback['stop_on_first_fail'] = $stop_on_first_fail;
		
		
		// boolean for show input for testcases
		$feedback['show_input'] = false;
		// boolean for show output for testcases
		$feedback['show_output'] = false;
		
		// array of short/long feedback
		$feedback['response'] = [];
		
			
		if($testcase_output_level == "Output"){
			$feedback['show_output'] = true;
			
		} else if($testcase_output_level == "Both"){
			$feedback['show_output'] = true;
			$feedback['show_input'] = true;
		}
		
		$feedback['highlights'] = [];
		$highlights = [];
		
		// loop through the results
		foreach($submission->testcaseresults as $tcr){

		
			if($tcr->testcase->is_extra_credit && !$extra_testcases_display){
				continue;
			}
			
			if($tcr->testcase->feedback != null && !$tcr->is_correct && $response_level == "Short"){
				
				$resp = trim($tcr->testcase->feedback->short_response);
				
				if($resp != ""){
					$feedback['response'][$tcr->testcase->seq_num] = $resp;
				}
			} else if($tcr->testcase->feedback != null && !$tcr->is_correct && $response_level == "Long"){
				
				$resp = trim($tcr->testcase->feedback->long_response);
				
				if($resp != ""){
					$feedback['response'][$tcr->testcase->seq_num] = $resp;
				}
			}
			
			
			if(!$tcr->is_correct){
				
				$index = -1;
				$indexEnd = -1;
				
				$exp = $tcr->testcase->correct_output;
				$usr = $tcr->std_output;
				
				$broken = false;
				
				for($i=0; $i<strlen($exp); $i++){
					
					
					if(!$broken && $i < strlen($usr) && $exp[$i] != $usr[$i]){
						$index = $i;
						$broken = true;
					}
					else if($broken && $i < strlen($usr) && $exp[$i] == $usr[$i]){
						$indexEnd = $i;
						break;
					}
					else if($i >= strlen($usr)){
						break;
					}
					
				}
				
				if($indexEnd == -1){
					$indexEnd = strlen($usr)-1;
				}

				$highlights[] = ['id' => $tcr->id, 'index' => $index, 'indexEnd' => $indexEnd];
			}
		}
		
		//die(json_encode($highlights));
		
		$feedback['highlights'] = $highlights;
			
		if(!$feedback['display_markers']){
			$feedback['response'] = array_unique($feedback['response']);
		}
		
		return $feedback;		
	}
		
	public function isAcceptedSubmission($submission, $previous){
		
		$count = 0;
		foreach($submission->testcaseresults->toArray() as $tcr){
			if($tcr->is_correct){
				$count++;
			}
		}
		
		// take the new solution if it is 100% no matter wha
		$total_testcases = count($submission->problem->testcases);
		
		if($count == $total_testcases){
			#echo "This new testcase solves all of the testcases!";
			return true;
		}
		// choose higher percentage if they both have percentages
		else if($previous && $submission->percentage > $previous->percentage){
			#echo "This new one has a higher percentage!";
			return true;
		}
		else {
			#echo "Only change if the old one isn't set";
			return $previous == null;
		}
		
	}
	
	
	
	# Contest Grading Methods
	public function getProblemScore($team, $problem, $elevatedUser){
		
		// return an array that contains these values:
		// num_attempts, time (in minutes) of submission
		// has_solved/not_solved, penalty_points_raw (not counting correct), 
		// penalty_points (including correct submission time)
		
		$score = [];
		
		if($elevatedUser){
			$time_max = $problem->assignment->section->end_time;
		} else {
			
			// if the override is set to less than the current time, use that as the max time
			if($problem->assignment->freeze_override 
				&& $problem->assignment->freeze_override_time 
				&& $problem->assignment->freeze_override_time < $problem->assignment->freeze_time){
					
				$time_max = $problem->assignment->freeze_override_time;
			} 
			// if the override is set but no time, there is no max time
			else if($problem->assignment->freeze_override) {
				$time_max = $problem->assignment->section->end_time;
			} 
			// normal scenario
			else {
				$time_max = $problem->assignment->freeze_time;
			}
		}
		
		// get submissions
		$qb_subs = $this->em->createQueryBuilder();
		$qb_subs->select('s')
			->from('AppBundle\Entity\Submission', 's')
			->where('s.problem = ?1')
			->andWhere('s.team = ?2')
			->andWhere('s.pending_status = ?3')
			->andWhere('s.is_completed = ?4')
			->andWhere('s.timestamp <= ?5')
			->setParameter(1, $problem)
			->setParameter(2, $team)
			->setParameter(3, 2)
			->setParameter(4, true)
			->setParameter(5, $time_max)
			->orderBy('s.timestamp', 'ASC');
			
		$subs_query = $qb_subs->getQuery();
		$subs = $subs_query->getResult();
		
		
		// get number of attempts
		// get penalty points raw
		$num_attempts = 0;
		$penalty_points_raw = 0;
		$correct_sub = null;
		
		foreach($subs as $sub){
			
			
			$num_attempts++;
			if($sub->isCorrect()){
				
				$correct_sub = $sub;
				break;
			}
			
			if($sub->wrong_override){
				$pen_type_val = $sub->problem->assignment->penalty_per_wrong_answer;
			}
			// compile error
			else if($sub->compiler_error){
				$pen_type_val = $sub->problem->assignment->penalty_per_compile_error;
			}
			// runtime error
			else if($sub->runtime_error){
				$pen_type_val = $sub->problem->assignment->penalty_per_runtime_error;
			}
			// time limit
			else if($sub->exceeded_time_limit){
				$pen_type_val = $sub->problem->assignment->penalty_per_time_limit;
			}
			// wrong answer
			else {
				$pen_type_val = $sub->problem->assignment->penalty_per_wrong_answer;
			}
			$penalty_points_raw += $pen_type_val;
		}
		
		$score['num_attempts'] = $num_attempts;
		
		
		// get time of sub
		// get correctness
		if(isset($correct_sub)){
			
			// time
			$contest_start = $problem->assignment->start_time;
			$sub_time = $correct_sub->timestamp;
			
			$time_diff = $sub_time->getTimestamp() - $contest_start->getTimestamp();

			$is_correct = true;
			$time_of_sub = max((int) ceil($time_diff / 60), 0);
					
			$score['penalty_points_raw'] = $penalty_points_raw;			
			$score['penalty_points'] = $time_of_sub + $penalty_points_raw;
			
		} else {
			
			$is_correct = false;
			$time_of_sub = -1;	

			$score['penalty_points_raw'] = -1;
			$score['penalty_points'] = -1;
		}
		
		$score['correct'] = $is_correct;
		$score['time'] = $time_of_sub;
		
		
		
		return $score;
	}
	
	public function getTeamScore($team, $elevatedUser){
		
		// returns an array of some pertinent information:
		// num_correct, total_penalty, array of subtimes, array of penalties
		
		$problems = $team->assignment->problems->toArray();
		
		$scores = [];
		
		foreach($problems as $problem){			
			$scores[] = $this->getProblemScore($team, $problem, $elevatedUser);			
		}
		
		$num_correct = 0;
		$total_penalty = 0;
		$times = [];
		$penalties = [];
		$raw_penalties = [];
		$results = [];
		$attempts = [];
		
		foreach($scores as $scr){
			
			$results[] = $scr['correct'];
			
			if($scr['correct']){
				
				$num_correct++;
				$total_penalty += $scr['penalty_points'];
			}
			
			$penalties[] = $scr['penalty_points'];
			$raw_penalties[] = $scr['penalty_points_raw'];
			$times[] = $scr['time'];	
			$attempts[] = $scr['num_attempts'];
		}
		
		$score = [];
		
		$score['team_id'] = $team->id;
		$score['team_name'] = $team->name;
		$score['num_correct'] = $num_correct;
		$score['total_penalty'] = $total_penalty;
		$score['results'] = $results; // boolean array of yes/no solved per problem
		$score['penalties'] = $penalties; // int array of penalties per problem
		$score['raw_penalties'] = $raw_penalties; // int array of penalties per problem without  
		$score['times'] = $times;
		$score['attempts'] = $attempts; // integer array of attempts per problem
		$score['rank'] = -1;
		
		return $score;
	}
	
	public function getLeaderboard($user, $assignment, $normal_user){
		
		
		$teams = $assignment->teams->toArray();
		
		$user_team = $this->getTeam($user, $assignment);
		
		$scores = [];
		
		
		
		if( is_object($user) ){
			
			$elevatedUser = ($user->hasRole("ROLE_SUPER") || $user->hasRole("ROLE_ADMIN") || $this->isJudging($user, $assignment->section)) && !$normal_user;
		
		} else {
			
			$elevatedUser = false;			
		}
		
		foreach($teams as $team){
			
			$elevatedTeam = $elevatedUser;			
			$scores[] = $this->getTeamScore($team, $elevatedTeam);
		}
		
		// sort the scores into the proper order
		usort($scores, array($this, 'compareTeamScoresNames'));
		
		$prevScore = null;
		$rank = 0;
		
		$count = 0;
		$user_index = -1;
		foreach($scores as &$scr){
			
			if($scr['team_id'] == $user_team->id){
				$user_index = $count;
			}
			$count++;
			
			if($prevScore && $this->compareTeamScores($prevScore, $scr) == 0){
				$rank = $prevRank;
			} else {
				$rank++;
			}
			
			$scr['rank'] = $rank;
			
			$prevRank = $rank;
			$prevScore = $scr;
		}
		
		$leaderboard['scores'] = $scores;
		$leaderboard['index'] = $user_index;
		
		
		$attempts_per_problem_count = [];
		$correct_submissions_per_problem_count = [];
		
		$probIndex = 0;
		// loop through each problem 
		foreach ($assignment->problems as $prob) {
			
			$correct_submissions_per_problem_count[$probIndex] = 0;
			$attempts_per_problem_count[$probIndex] = 0;
			
			foreach($scores as $team_score){
				
				$prob_correct_maybe = $team_score["results"];
				$ps = $prob_correct_maybe[$probIndex];
				
				if ( $ps == true) {
					$correct_submissions_per_problem_count[$probIndex]++;
				}
				
				$att = $team_score["attempts"];
				$attempts_per_problem_count[$probIndex] += $att[$probIndex];
				
			}
			
			$probIndex++;
		}
		
		$leaderboard['attempts_per_problem_count'] = $attempts_per_problem_count;
		$leaderboard['correct_submissions_per_problem_count'] = $correct_submissions_per_problem_count;
		
		
		return $leaderboard;
	}
	
	private static function compareTeamScoresNames($a, $b){
				
		// compares two teams with the following tiebreakers:
		// 1) team with most correct submissions
		// 2) team with the fewest penalty points
		// 3) team with the quickest final submission, 2nd-to-last submission, ...
		
		if($a['num_correct'] == $b['num_correct']){
		
			if($a['total_penalty'] == $b['total_penalty']){
				
				$a_times = $a['times'];
				$b_times = $b['times'];
				
				rsort($a_times);
				rsort($b_times);
				
				// go through the times from max to min
				for($i=0; $i<count($a_times); $i++){
					
					if($a_times[$i] != $b_times[$i]){						
						return ($a_times[$i] > $b_times[$i]) ? -1 : 1;												
					}
				}
				
				// they are equal
				return strcmp($a['team_name'], $b['team_name']);
				
			} else {
				
				return ($a['total_penalty'] < $b['total_penalty']) ? -1 : 1;				
			}
			
		} else {
			
			return ($a['num_correct'] > $b['num_correct']) ? -1 : 1;
		}
		
	}
	
	private static function compareTeamScores($a, $b){	
		
		// compares two teams with the following tiebreakers:
		// 1) team with most correct submissions
		// 2) team with the fewest penalty points
		// 3) team with the quickest final submission, 2nd-to-last submission, ...
		
		if($a['num_correct'] == $b['num_correct']){
		
			if($a['total_penalty'] == $b['total_penalty']){
				
				$a_times = $a['times'];
				$b_times = $b['times'];
				
				rsort($a_times);
				rsort($b_times);
				
				// go through the times from max to min
				for($i=0; $i<count($a_times); $i++){
					
					if($a_times[$i] != $b_times[$i]){						
						return ($a_times[$i] > $b_times[$i]) ? -1 : 1;												
					}
				}
				
				// they are equal
				return 0;
				
			} else {
				
				return ($a['total_penalty'] < $b['total_penalty']) ? -1 : 1;				
			}
			
		} else {
			
			return ($a['num_correct'] > $b['num_correct']) ? -1 : 1;
		}
	}	
}

?>