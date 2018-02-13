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


class ContestPollingController extends Controller {

	public function pollContestAction(Request $request){
		
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
		
		$section = $contest->section;
		
		# validation
		$elevatedUser = $grader->isJudging($user, $section) || $user->hasRole("ROLE_SUPER") || $user->hasRole("ROLE_ADMIN");
		
		// elevated or taking and active
		if( !($elevatedUser || ($grader->isTaking($user, $section) && $section->isActive())) ){
			return $this->returnForbiddenResponse("PERMISSION DENIED");
		}
				
		# get the queries
		if($grader->isJudging($user, $contest->section) || $user->hasRole("ROLE_ADMIN") || $user->hasRole("ROLE_SUPER")){
			$extra_query = "OR 1=1";
		} else {
			$extra_query = "";
			$team = $grader->getTeam($user, $contest);
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
		
		$checklist = ["hi", "hello", "hey"];
	
		$response = new Response(json_encode([
			'clarifications' => $queries,
			'checklist' => $checklist,
		]));
			
		
		$response->headers->set('Content-Type', 'application/json');
		$response->setStatusCode(Response::HTTP_OK);

		return $response;
	}
	
	public function pollScoreboardAction(Request $request){
		$em = $this->getDoctrine()->getManager();
		$grader = new Grader($em);

		$user = $this->get('security.token_storage')->getToken()->getUser();
		if(!$user){
			return $this->returnForbiddenResponse('User does not exist.');
		}
		
		# post data
		$postData = $request->request->all();
		
		$assignment = $em->find('AppBundle\Entity\Assignment', $postData['contestId']);		
		if(!$assignment){
			return $this->returnForbiddenResponse('Contest ID is not valid.');
		}		
		
		$section = $assignment->section;
		
		# validation
		if( is_object($user) ){
		
			$elevatedUser = $grader->isJudging($user, $section) || $user->hasRole("ROLE_SUPER") || $user->hasRole("ROLE_ADMIN");
		
		} else {
		
			$elevatedUser = false;		
		}
		
		
		# elevated or section active
		if( !($section->isActive()) ){			
			return $this->returnForbiddenResponse("PERMISSION DENIED");
		}				
		
		# GET STUFF
		
		# leaderboard and totals
		$leaderboard = $grader->getLeaderboard($user, $assignment, $postData['normal_user']);
		
		# scoreboard frozen status
		$frozen_override = false;
		$unfrozen_override = false;
		// if the override is set and theres is a time, its frozen
		if($assignment->freeze_override && $assignment->freeze_override_time){				
			$frozen_override = true;				
		} 
		// if the override is set but no time, its unfrozen
		else if($assignment->freeze_override) {			
			$unfrozen_override = true;	
		}		
		
		# determine if a page refresh is needed
		if((isset($postData['end_time']) && $postData['end_time'] != $assignment->end_time->format('U'))
			|| (isset($postData['start_time']) && $postData['start_time'] != $assignment->start_time->format('U'))
			|| (isset($postData['freeze_time']) && $postData['freeze_time'] != $assignment->freeze_time->format('U'))){
			$page_refresh = true;
		} else {
			$page_refresh = false;
		}
	
		$response = new Response(json_encode([
			'leaderboard' => $leaderboard,
			'frozen_override' => $frozen_override,
			'unfrozen_override' => $unfrozen_override,
			'page_refresh' => $page_refresh,
		]));
		
		$response->headers->set('Content-Type', 'application/json');
		$response->setStatusCode(Response::HTTP_OK);

		return $response;
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

		$section = $contest->section;
		
		# validation
		$elevatedUser = $grader->isJudging($user, $section) || $user->hasRole("ROLE_SUPER") || $user->hasRole("ROLE_ADMIN");

		if( !($elevatedUser) ){
			
			return $this->returnForbiddenResponse("PERMISSION DENIED");
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
			->andWhere('s.team IS NOT NULL')
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
	
	private function returnForbiddenResponse($message){		
		$response = new Response($message);
		$response->setStatusCode(Response::HTTP_FORBIDDEN);
		return $response;
	}
	
}

?>
