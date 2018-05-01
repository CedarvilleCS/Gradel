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

use Symfony\Component\Config\Definition\Exception\Exception;

use Psr\Log\LoggerInterface;

class AssignmentController extends Controller {

	public function assignmentAction($sectionId, $assignmentId, $problemId) {

		$em = $this->getDoctrine()->getManager();
		
		$user = $this->get('security.token_storage')->getToken()->getUser();  	  
		if(!get_class($user)){
			die("USER DOES NOT EXIST!");		  
		}
		
		# get the section
		if(!isset($sectionId) || !($sectionId > 0)){
			die("SECTION ID WAS NOT PROVIDED OR FORMATTED PROPERLY");
		}
		
		$section_entity = $em->find("AppBundle\Entity\Section", $sectionId);
		if(!$section_entity){
			die("SECTION DOES NOT EXIST");
		}
		
		# REDIRECT TO CONTEST IF NEED BE
		if($section_entity->course->is_contest){
			
			if(isset($problemId)){
				return $this->redirectToRoute('contest', ['contestId' => $sectionId, 'roundId' => $assignmendId]);
			} else {
				return $this->redirectToRoute('contest_problem', ['contestId' => $sectionId, 'roundId' => $assignmendId, 'problemId' => $problemId]);
			}
		}
		
		# get the assignment
		if(!isset($assignmentId) || !($assignmentId > 0)){
			die("ASSIGNMENT ID WAS NOT PROVIDED OR FORMATTED PROPERLY");
		}
		
		$assignment_entity = $em->find("AppBundle\Entity\Assignment", $assignmentId);
		if(!assignment_entity){
			die("ASSIGNMENT DOES NOT EXIST");
		}
				
		if($problemId == 0){
			$problemId = $assignment_entity->problems[0]->id;
		}
		
		if($problemId != null){
			
			if(!($problemId > 0)){
				die("PROBLEM ID WAS NOT FORMATTED PROPERLY");
			}
			
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
			
			$problem_languages = $problem_entity->problem_languages;

			$languages = [];
			$ace_modes = [];
			$filetypes = [];
			foreach($problem_languages as $pl){
				
				$languages[] = $pl->language;
				
				$ace_modes[$pl->language->name] = $pl->language->ace_mode;
				$filetypes[str_replace(".", "", $pl->language->filetype)] = $pl->language->name;
			}
			
		}
		
		$grader = new Grader($em);		
		
		# figure out how many attempts they have left
		$total_attempts = $problem_entity->total_attempts;
		if($total_attempts == 0 || $grader->isTeaching($user, $assignment_entity->section) || $grader->isJudging($user, $assignment_entity->section)){
			$attempts_remaining = -1;
		} else {
			$attempts_remaining = max($total_attempts - $grader->getNumTotalAttempts($user, $problem_entity), 0);
		}
		
		# get the team
		$team = $grader->getTeam($user, $assignment_entity);
		
		if(!isset($team)){
			$whereClause = 's.user = ?1';
			$teamOrUser = $user;
		} else {
			$whereClause = 's.team = ?1';
			$teamOrUser = $team;
		}
		
		# get the get the best submission so far
		$qb_accsub = $em->createQueryBuilder();
		$qb_accsub->select('s')
			->from('AppBundle\Entity\Submission', 's')
			->where($whereClause)
			->andWhere('s.problem = ?2')
			->andWhere('s.best_submission = true')
			->setParameter(1, $teamOrUser)
			->setParameter(2, $problem_entity);		
			
		$sub_query = $qb_accsub->getQuery();
		$best_submission = $sub_query->getOneOrNullResult();

		# get the code from the last submissions
		$qb_allsubs = $em->createQueryBuilder();
		$qb_allsubs->select('s')
			->from('AppBundle\Entity\Submission', 's')
			->where($whereClause)
			->andWhere('s.problem = ?2')
			->orderBy('s.id', 'DESC')
			->setParameter(1, $teamOrUser)
			->setParameter(2, $problem_entity);
		$subs_query = $qb_allsubs->getQuery();
		$all_submissions = $subs_query->getResult();
		
		# get the user's trial for this problem
		$qb_trial = $em->createQueryBuilder();
		$qb_trial->select('t')
				->from('AppBundle\Entity\Trial', 't')
				->where('t.user = ?1')
				->andWhere('t.problem = ?2')
				->setParameter(1, $user)
				->setParameter(2, $problem_entity);

		$trial_query = $qb_trial->getQuery();
		$trial = $trial_query->getOneorNullResult();
		
		
		if(isset($_GET["submissionId"]) && $_GET["submissionId"] > 0){
			
			$submission = $em->find("AppBundle\Entity\Submission", $_GET["submissionId"]);
			
			if($submission->user != $user || $submission->problem != $problem_entity){
				die("You are not allowed to edit this submission on this problem!");
			}
						
			if(!$trial){
				$trial = new Trial();
				
				$trial->user = $user;
				$trial->problem = $problem_entity;		
				$trial->show_description = true;
				
				$em->persist($trial);
			}
			
			$trial->file = $submission->submitted_file;						
			
			$trial->language = $submission->language;	
			$trial->filename = $submission->filename;
			$trial->main_class = $submission->main_class_name;
			$trial->package_name = $submission->package_name;
			$trial->last_edit_time = new \DateTime("now");
			
			$em->flush();
			
			return $this->redirectToRoute('assignment', ['sectionId' => $section_entity->id, 'assignmentId' => $assignment_entity->id, 'problemId' => $problem_entity->id]);
		}
		
		# GET ALL USERS
		$qb_user = $em->createQueryBuilder();
		$qb_user->select('usr')
			->from('AppBundle\Entity\UserSectionRole', 'usr')
			->where('usr.section = ?1')
			->setParameter(1, $section_entity);

		$user_query = $qb_user->getQuery();
		$usersectionroles = $user_query->getResult();

		$section_takers = [];
		$section_teachers = [];
		$section_helpers = [];
		$section_judges = [];

		foreach($usersectionroles as $usr){
			if($usr->role->role_name == "Takes"){
				$section_takers[] = $usr->user;
			} else if($usr->role->role_name == "Teaches"){
				$section_teachers[] = $usr->user;
			} else if($usr->role->role_name == "Helps"){
				$section_helpers[] = $usr->user;
			} else if($usr->role->role_num == "Judges"){
				$section_judges[] = $usr->user;
			}
		}	
		
		
		return $this->render('assignment/index.html.twig', [
			'user' => $user,
			'team' => $team,
			'section' => $assignment_entity->section,
			'assignment' => $assignment_entity,
			'problem' => $problem_entity,
			'section_takers' => $section_takers,
			'user_impersonators' => $section_takers,
			'languages' => $languages,
			'usersectionrole' => $usersectionrole,
			'grader' => new Grader($em),
			'grades' => $grades,
			'attempts_remaining' => $attempts_remaining,
			'best_submission' => $best_submission,
			'trial' => $trial,
			'all_submissions' => $all_submissions,

			'ace_modes' => $ace_modes,
			'filetypes' => $filetypes,
		]);

	}

    public function editAction($sectionId, $assignmentId) {

		$em = $this->getDoctrine()->getManager();
		
		if(!isset($sectionId) || !($sectionId > 0)){
			die("SECTION ID WAS NOT PROVIDED OR NOT FORMATTED PROPERLY");
		}

		$section = $em->find('AppBundle\Entity\Section', $sectionId);	
		if(!$section){
			die("SECTION DOES NOT EXIST");
		}
		
		if($section->course->is_contest){
			return $this->redirectToRoute('contest_edit', ['contestId' => $sectionId]);
		}
		
		$user = $this->get('security.token_storage')->getToken()->getUser();  	  
		if(!get_class($user)){
			die("USER DOES NOT EXIST!");		  
		}
		
		# validate the user
		$grader = new Grader($em);
		if(!$user->hasRole("ROLE_SUPER") && !$user->hasRole("ROLE_ADMIN") && !$grader->isTeaching($user, $section) && !$grader->isJudging($user, $section)){
			die("YOU ARE NOT ALLOWED TO EDIT THIS ASSIGNMENT");			
		}		
		
		if($assignmentId != 0){
			
			if(!($assignmentId > 0)){
				die("ASSIGNMENT ID WAS NOT FORMATTED PROPERLY");
			}
									
			$assignment = $em->find('AppBundle\Entity\Assignment', $assignmentId);
			
			if(!$assignment || $section != $assignment->section){
				die("Assignment does not exist or does not belong to given section");
			}
		}

		$assignments = 	$em->getRepository('AppBundle\Entity\Assignment')->findBy(['clone_hash'=>$assignment->clone_hash]);
		$sections = [];

		if($section->master){
			$sections = $section->master->slaves->toArray();
			$sections[] = $section->master;
		} else {
			$sections = $section->slaves->toArray();
			$sections[] = $section;
		}

		usort($assignments, function($a, $b){
			return strcmp($a->section->name, $b->section->name);
		});

		usort($sections, function($a, $b){
			return strcmp($a->name, $b->name);
		});

		return $this->render('assignment/edit.html.twig', [
			
			"assignment" => $assignment,
			"assignments" => $assignments,

			"section" => $section,
			"sections" => $sections,

			"edit" => true,
		]);
    }

    public function deleteAction($sectionId, $assignmentId){
	
		$em = $this->getDoctrine()->getManager();
		
		# get the assignment
		if(!isset($assignmentId) || !($assignmentId > 0)){
			die("ASSIGNMENT ID WAS NOT PROVIDED OR FORMATTED PROPERLY");
		}
		
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
		if(!$user->hasRole("ROLE_SUPER") && !$user->hasRole("ROLE_ADMIN") && !$grader->isTeaching($user, $assignment->section) && !$grader->isJudging($user, $assignment->section)){
			die("YOU ARE NOT ALLOWED TO DELETE THIS ASSIGNMENT");			
		}
		
		$assignments = 	$em->getRepository('AppBundle\Entity\Assignment')->findBy(['clone_hash'=>$assignment->clone_hash]);
		
		foreach($assignments as &$asgn){
			$em->remove($asgn);
		}
		
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
		# get the assignment
		if(!isset($postData['section']) || !($postData['section'] > 0)){
			return $this->returnForbiddenResponse("SECTION ID WAS NOT PROVIDED OR FORMATTED PROPERLY");
		}
		
		$section = $em->find('AppBundle\Entity\Section', $postData['section']);		
		if(!$section){
			return $this->returnForbiddenResponse("Section ".$postData['section']." does not exist");
		}

		if($section->master){
			$sections = $section->master->slaves->toArray();
			$sections[] = $section->master;
		} else {
			$sections = $section->slaves->toArray();
			$sections[] = $section;
		}
		
		# only super users/admins/teacher can make/edit an assignment
		$grader = new Grader($em);		
		if(!$user->hasRole("ROLE_SUPER") && !$user->hasRole("ROLE_ADMIN") && !$grader->isTeaching($user, $section) && !$grader->isJudging($user, $section)){			
			return $this->returnForbiddenResponse("You do not have permission to make an assignment.");
		}		
		
		# check mandatory fields
		if(!isset($postData['name']) || !isset($postData['open_time']) || !isset($postData['close_time']) || !isset($postData['teams'])){
			return $this->returnForbiddenResponse("Not every required field is provided.");			
		} else {
			
			# validate the weight if there is one
			if(	is_numeric(trim($postData['weight'])) && 
				((int)trim($postData['weight']) < 0 || $postData['weight'] % 1 != 0)){
					
				return $this->returnForbiddenResponse("The provided weight ".$postData['weight']." is not permitted.");
			}

			# validate the penalty if there is one
			if(is_numeric(trim($postData['penalty'])) && 
				((float)trim($postData['penalty']) > 1.0 || (float)trim($postData['penalty']) < 0.0)){
					
				return $this->returnForbiddenResponse("The provided penalty ".$postData['penalty']." is not permitted.");
			}			
		}		
		
		# build teams
		$jsonAssignmentsTeams = json_decode($postData['teams']);

		$assignments = [];
		
		$em->getConnection()->beginTransaction();

		try{
			# create new assignment
			if($postData['assignment'] == 0){
									
				$temp = [''];
				while(count($temp) > 0){
					$hash = random_int(-2147483648, 2147483647);

					$temp = $em->getRepository('AppBundle\Entity\Assignment')->findBy(['clone_hash'=>$hash]);	
				}

				foreach($sections as $sect){
					$asgn = new Assignment();		

					$asgn->clone_hash = $hash;
					$asgn->section = $sect;

					$em->persist($asgn);
					$assignments[] = $asgn;
				}

			} else {
				
				if(!isset($postData['assignment']) || !($postData['assignment'] > 0)){
					die("ASSIGNMENT ID WAS NOT PROVIDED OR FORMATTED PROPERLY");
				}
				
				$asgn = $em->find('AppBundle\Entity\Assignment', $postData['assignment']);
				
				if(!$asgn || $section != $asgn->section){
					throw new Exception("Assignment ".$postData['assignment']." does not exist for the given section.");
				}
				
				$assignments = 	$em->getRepository('AppBundle\Entity\Assignment')->findBy(['clone_hash'=>$asgn->clone_hash]);	
			}

			# TIMES
			$openTime = DateTime::createFromFormat("m/d/Y H:i:s", $postData['open_time'].":00");
			$closeTime = DateTime::createFromFormat("m/d/Y H:i:s", $postData['close_time'].":00");
			
			if(!isset($openTime) || $openTime->format("m/d/Y H:i") != $postData['open_time']){
				throw new Exception("Provided opening time ".$postData['open_time']." is not valid.");
			}
			
			if(!isset($closeTime) || $closeTime->format("m/d/Y H:i") != $postData['close_time']){
				throw new Exception("Provided closing time ".$postData['close_time']." is not valid.");
			}
			
			if(isset($postData['cutoff_time']) && $postData['cutoff_time'] != ""){
				
				$cutoffTime = DateTime::createFromFormat("m/d/Y H:i:s", $postData['cutoff_time'].":00");
				
				if(!isset($cutoffTime) || $cutoffTime->format("m/d/Y H:i") != $postData['cutoff_time']){
					throw new Exception("Provided cutoff time ".$postData['cutoff_time']." is not valid.");
				}
				
			} else {
				$cutoffTime = $closeTime;
			}
						
			if($cutoffTime < $closeTime || $closeTime < $openTime){
				throw new Exception("Provided times are not valid. The closing time must be after the opening time.");			
			}

			# PENALTY
			$penalty = (float)trim($postData['penalty']);		

			# EXTRA CREDIT
			if(isset($postData["is_extra_credit"]) && $postData["is_extra_credit"] == "true"){
				$is_extra_credit = true;
			} else {			
				$is_extra_credit = false;
			}

			$count = 0;
			foreach($assignments as &$assignment){

				# set the necessary fields
				$assignment->name = trim($postData['name']);
				$assignment->description = trim($postData['description']);
								
				$assignment->start_time = $openTime;
				$assignment->end_time = $closeTime;
				$assignment->cutoff_time = $cutoffTime;
				
				# set the weight
				if(isset($postData['weight'])){
					$assignment->weight = (int)trim($postData['weight']);
				} else {
					$assignment->weight = 1;
				}				
						
				# set extra credit
				$assignment->is_extra_credit = $is_extra_credit;
				
				# set grading penalty
				$assignment->penalty_per_day = $penalty;	

				$old_teams = [];

				foreach($assignment->teams->toArray() as $team){
					$old_teams[$team->id] = $team;
				}

				$count = 0;		
				$section_takers = $assignment->section->getTakers();


				$jsonTeams = $jsonAssignmentsTeams->{$assignment->section->id};

				foreach($jsonTeams as $jsonTeam){
					
					// editing a current team
					if($jsonTeam->id != 0){
						
						$team = $em->find('AppBundle\Entity\Team', $jsonTeam->id);

						if(!$team || $team->assignment != $assignment){
							throw new Exception("Team with id ".$team." does not exist for this assignment");
						}
						
						unset($old_teams[$team->id]);	

					} else {
						$team = new Team($jsonTeam->name, $assignment);
					}

					$team->name = $jsonTeam->name;				
					$team->users = new ArrayCollection();
					
					foreach($jsonTeam->members as $jsonMember){
						
						$temp_user = $em->find('AppBundle\Entity\User', $jsonMember);

						if(!$temp_user || !in_array($temp_user, $section_takers)){
							throw new Exception("User with id ".$jsonMember." does not take this class");
						}

						$team->users->add($temp_user);										
					}
					
					if($team->users->count() < 1){
						throw new Exception($team->name." did not have any users provided");
					}
					
					$assignment->teams->add($team);	
				}

				# remove the old teams that no longer exist
				foreach($old_teams as $old){			
					$em->remove($old);	
					$em->flush();			
				}

				$count++;
			}
				
			$em->flush();
			$em->getConnection()->commit();

		} catch(Exception $e) {

			$em->getConnection()->rollBack();
			return $this->returnForbiddenResponse($e->getMessage());
		}

		$url = $this->generateUrl('assignment', ['sectionId' => $assignment->section->id, 'assignmentId' => $assignment->id]);
				
		$response = new Response(json_encode(array('redirect_url' => $url, 'assignment' => $assignment)));
		$response->headers->set('Content-Type', 'application/json');
		$response->setStatusCode(Response::HTTP_OK);
		

		return $response;
	}
	
	public function clearSubmissionsAction(Request $request){

		$em = $this->getDoctrine()->getManager();
				
		# validate the current user
		$user = $this->get('security.token_storage')->getToken()->getUser();
		if(!$user){			
			return $this->returnForbiddenResponse("You are not a user.");
		}
		
		# see which fields were included
		$postData = $request->request->all();
		
		# get the current section
		# get the assignment
		if(!isset($postData['assignment'])){
			return $this->returnForbiddenResponse("Assignment ID was not provided");
		}
		
		$assignment = $em->find('AppBundle\Entity\Assignment', $postData['assignment']);		
		if(!$assignment){
			return $this->returnForbiddenResponse("Section ".$postData['assignment']." does not exist");
		}

		$section = $assignment->section;
		
		# only super users/admins/teacher can make/edit an assignment
		$grader = new Grader($em);		
		if( !($user->hasRole("ROLE_SUPER") || $user->hasRole("ROLE_ADMIN") || $grader->isTeaching($user, $section) || $grader->isJudging($user, $section)) ){			
			return $this->returnForbiddenResponse("You do not have permission to do this.");
		}

		$assignments = 	$em->getRepository('AppBundle\Entity\Assignment')->findBy(['clone_hash'=>$assignment->clone_hash]);
		$result = 0;
		// delete all submission but keep all of the trials
		foreach($assignments as $asgn){
						
			$qb = $em->createQueryBuilder();
			$qb->delete('AppBundle\Entity\Submission', 's');
			$qb->where('s.problem IN (?1)');
			$qb->setParameter(1, $asgn->problems->toArray());

			$result += $qb->getQuery()->getResult();
		}

		$em->flush();

		$response = new Response(json_encode([
			"result" => $result,
		]));

		$response->headers->set('Content-Type', 'application/json');
		$response->setStatusCode(Response::HTTP_OK);
		
		return $response;				
	}

	public function clearTrialsAction(Request $request){

		$em = $this->getDoctrine()->getManager();
				
		# validate the current user
		$user = $this->get('security.token_storage')->getToken()->getUser();
		if(!$user){			
			return $this->returnForbiddenResponse("You are not a user.");
		}
		
		# see which fields were included
		$postData = $request->request->all();
		
		# get the current section
		# get the assignment
		if(!isset($postData['assignment'])){
			return $this->returnForbiddenResponse("Assignment ID was not provided");
		}
		
		$assignment = $em->find('AppBundle\Entity\Assignment', $postData['assignment']);		
		if(!$assignment){
			return $this->returnForbiddenResponse("Section ".$postData['assignment']." does not exist");
		}

		$section = $assignment->section;
		
		# only super users/admins/teacher can make/edit an assignment
		$grader = new Grader($em);		
		if( !($user->hasRole("ROLE_SUPER") || $user->hasRole("ROLE_ADMIN") || $grader->isTeaching($user, $section) || $grader->isJudging($user, $section)) ){			
			return $this->returnForbiddenResponse("You do not have permission to do this.");
		}

		$assignments = 	$em->getRepository('AppBundle\Entity\Assignment')->findBy(['clone_hash'=>$assignment->clone_hash]);
		$result = 0;
		// delete all submission but keep all of the trials
		foreach($assignments as $asgn){
				
			// delete all submission but keep all of the trials
			$qb = $em->createQueryBuilder();
			$qb->delete('AppBundle\Entity\Trial', 't');
			$qb->where('t.problem IN (?1)');
			$qb->setParameter(1, $asgn->problems->toArray());

			$result += $qb->getQuery()->getResult();
		}

		$em->flush();

		$response = new Response(json_encode([
			"result" => $result,
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
