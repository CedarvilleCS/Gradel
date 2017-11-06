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
use AppBundle\Entity\ProblemGradingMethod;
use AppBundle\Entity\AssignmentGradingMethod;
use AppBundle\Entity\Feedback;
use AppBundle\Entity\TestcaseResult;

use \DateTime;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;

class Grader  {
	
	public $em;
	
	public function __construct($em) {
		
		if(get_class($em) != "Doctrine\ORM\EntityManager"){
			throw new Exception('The Grader class must be given a Doctrine\ORM\EntityManager but was given '.get_class($em));
		}
		
		$this->em = $em;		
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
		foreach($team_entities as $tm){				
			foreach($tm->users as $us){		
			
				if($user->id == $us->id){
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
		$grades['total_testcases'] = $total_testcases;
			
				
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
			
		$grades['all_submissions'] = $subs;	
		
		$attempts = $this->getNumAttempts($user, $problem);				
		$grades['attempts'] = $attempts;		
		
		# temp values
		$grades['passed_testcases'] = 0;
		$grades['percentage_raw'] = 0;
		$grades['percentage_adj'] = 0;
		
		if($accepted_sub){
			
			# test cases passed
			$passed_testcases = 0;
			foreach($accepted_sub->testcaseresults as $tcr){
				if($tcr->is_correct){
					$passed_testcases++;
				}
			}
			$grades['passed_testcases'] = $passed_testcases;		
			
			# percentage grade - raw
			$grades['percentage_raw'] = (float)$accepted_sub->percentage;
			
			# percentage grade - after mods
			$gradingmethod = $problem->gradingmethod;				
			$num_before_penalty = $gradingmethod->attempts_before_penalty;
			$penalty_percentage = $gradingmethod->penalty_per_attempt;
			
			$attempts_to_penalize = max($attempts - $num_before_penalty, 0);
			$total_penalty = $attempts_to_penalize*$penalty_percentage;
			
			$adjusted_percentage = max($accepted_sub->percentage - $total_penalty, 0);
			$grades['percentage_adj'] = $adjusted_percentage;
		}
		
		return $grades;		
	}
	
	public function getAllProblemGrades($user, $assignment){
		
		$problems = [];
		
		foreach($assignment->problems as $problem){
			
			$problems[$problem->id] = $this->getProblemGrade($user, $problem);
		}

		return $problems;		
	}
	
	public function getAssignmentGrade($user, $assignment){
		
		$grade = [];
	
		$normal_problem_count = 0;
		$extra_problem_count = 0;
		foreach($assignment->problems as $problem){
			
			if($problem->is_extra_credit){
				$extra_problem_count++;
			} else{
				$normal_problem_count++;
			}			
		}	
	
		$num_correct_assignments = 0;
		$num_extra_correct_assignments = 0;
		
		$grade['num_problems'] = $normal_problem_count;
		$grade['num_extra_problems'] = $extra_problem_count;
		$grade['num_correct'] = 0;
		$grade['num_extra_correct'] = 0;
		$grade['percentage_raw'] = 0.0;
		$grade['percentage_adj'] = 0.0;
		
		$problem_grades = $this->getAllProblemGrades($user, $assignment);
		$grade['problem_grades'] = $problem_grades;
		
		$assignment_percentage = 0.0;
		foreach($assignment->problems as $problem){	
		
			$problem_percentage = $problem_grades[$problem->id]['percentage_adj'];
			if($problem->weight == 0){
				$assignment_percentage += $problem_percentage*(1.0/$normal_problem_count);
			} else{		
				$assignment_percentage += $problem_percentage*$problem->weight;
			}
			
			if($problem_grades[$problem->id]['passed_testcases'] == $problem_grades[$problem->id]['total_testcases']){
				
				if($problem->is_extra_credit){
					$num_extra_correct_assignments++;
				} else {
					$num_correct_assignments++;
				}
			}
		}
		
		$grade['num_correct'] = $num_correct_assignments;
		$grade['num_extra_correct'] = $num_extra_correct_assignments;
	
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
	
		$grade['percentage_adj'] = max($assignment_percentage - $num_days_over*$assignment->gradingmethod->penalty_per_day, 0);
		#echo $grade['percentage_adj']."<br/>";
		#echo $assignment_grade['num_problems']."<br/>";
		#echo $assignment_grade['finished_problems']."<br/>";
		
		return $grade;
	}
}










?>