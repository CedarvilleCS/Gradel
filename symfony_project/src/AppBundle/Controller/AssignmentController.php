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

			'default_code' => $default_code,
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
			return $this->returnForbiddenResponse('contest_edit', ['contestId' => $sectionId]);
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
				
		# get all the users taking the course
		$takes_role = $em->getRepository('AppBundle\Entity\Role')->findOneBy(array('role_name' => 'Takes'));
		$builder = $em->createQueryBuilder();
		$builder->select('u')
			  ->from('AppBundle\Entity\UserSectionRole', 'u')
			  ->where('u.section = ?1')
			  ->andWhere('u.role = ?2')
			  ->setParameter(1, $section)
			  ->setParameter(2, $takes_role);
		$query = $builder->getQuery();
		$section_taker_roles = $query->getResult();

		$students = [];
		foreach($section_taker_roles as $usr){
			$student = [];
			
			$student['id'] = $usr->user->id;
			$student['name'] = $usr->user->getFirstName()." ".$usr->user->getLastName();
			
			$students[] = $student;
		}

		return $this->render('assignment/edit.html.twig', [
			"assignment" => $assignment,
			"section" => $section,
			"edit" => true,
			"students" => $students,
		]);
    }

    public function deleteAction($sectionId, $assignmentId){
	
		$em = $this->getDoctrine()->getManager();
		
		# get the assignment
		if(!isset($assignmentId) || !($assigmentId > 0)){
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
		# get the assignment
		if(!isset($postData['section']) || !($postData['section'] > 0)){
			die("SECTION ID WAS NOT PROVIDED OR FORMATTED PROPERLY");
		}
		
		$section = $em->find('AppBundle\Entity\Section', $postData['section']);		
		if(!$section){
			return $this->returnForbiddenResponse("Section ".$postData['section']." does not exist");
		}
		
		# only super users/admins/teacher can make/edit an assignment
		$grader = new Grader($em);		
		if(!$user->hasRole("ROLE_SUPER") && !$user->hasRole("ROLE_ADMIN") && !$grader->isTeaching($user, $section) && !$grader->isJudging($user, $section)){			
			return $this->returnForbiddenResponse("You do not have permission to make an assignment.");
		}		
		
		# check mandatory fields
		if(!isset($postData['name']) || !isset($postData['open_time']) || !isset($postData['close_time']) || !isset($postData['teams']) || !isset($postData['teamnames'])){
			return $this->returnForbiddenResponse("Not every required field is provided.");			
		} else {
			
			# validate the weight if there is one
			if(	is_numeric(trim($postData['weight'])) && 
				((int)trim($postData['weight']) < 1 || $postData['weight'] % 1 != 0)){
					
				return $this->returnForbiddenResponse("The provided weight ".$postData['weight']." is not permitted.");
			}

			# validate the penalty if there is one
			if(is_numeric(trim($postData['penalty'])) && 
				((float)trim($postData['penalty']) > 1.0 || (float)trim($postData['penalty']) < 0.0)){
					
				return $this->returnForbiddenResponse("The provided penalty ".$postData['penalty']." is not permitted.");
			}			
		}		
		
		# create new assignment
		if($postData['assignment'] == 0){
			$assignment = new Assignment();		
			$em->persist($assignment);
			
		} else {
			
			if(!isset($postData['assignment']) || !($postData['assignment'] > 0)){
				die("ASSIGNMENT ID WAS NOT PROVIDED OR FORMATTED PROPERLY");
			}
			
			$assignment = $em->find('AppBundle\Entity\Assignment', $postData['assignment']);
			
			if(!$assignment || $section != $assignment->section){
				return $this->returnForbiddenResponse("Assignment ".$postData['assignment']." does not exist for the given section.");
			}			
		}
		
		# set the necessary fields
		$assignment->name = trim($postData['name']);
		$assignment->description = trim($postData['description']);
		$assignment->section = $section;
		
		# set the times		
		$openTime = DateTime::createFromFormat("m/d/Y H:i:s", $postData['open_time'].":00");
		$closeTime = DateTime::createFromFormat("m/d/Y H:i:s", $postData['close_time'].":00");
		
		if(!isset($openTime) || $openTime->format("m/d/Y H:i") != $postData['open_time']){
			return $this->returnForbiddenResponse("Provided opening time ".$postData['open_time']." is not valid.");
		}
		
		if(!isset($closeTime) || $closeTime->format("m/d/Y H:i") != $postData['close_time']){
			return $this->returnForbiddenResponse("Provided closing time ".$postData['close_time']." is not valid.");
		}
		
		if(isset($postData['cutoff_time']) && $postData['cutoff_time'] != ""){
			
			$cutoffTime = DateTime::createFromFormat("m/d/Y H:i:s", $postData['cutoff_time'].":00");
			
			if(!isset($cutoffTime) || $cutoffTime->format("m/d/Y H:i") != $postData['cutoff_time']){
				return $this->returnForbiddenResponse("Provided cutoff time ".$postData['cutoff_time']." is not valid.");
			}
			
		} else {
			$cutoffTime = $closeTime;
		}
		
		
		if($cutoffTime < $closeTime || $closeTime < $openTime){
			return $this->returnForbiddenResponse("Provided times are not valid. The closing time must be after the opening time.");			
		}
		
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
		if(isset($postData["is_extra_credit"]) && $postData["is_extra_credit"] == "true"){
			$assignment->is_extra_credit = true;
		} else {			
			$assignment->is_extra_credit = false;
		}
		
		# set grading penalty
		$penalty = (float)trim($postData['penalty']);		
		$assignment->penalty_per_day = $penalty;		
	
		# get all the users taking the course
		$takes_role = $em->getRepository('AppBundle\Entity\Role')->findOneBy(array('role_name' => 'Takes'));
		$builder = $em->createQueryBuilder();
		$builder->select('u')
			  ->from('AppBundle\Entity\UserSectionRole', 'u')
			  ->where('u.section = ?1')
			  ->andWhere('u.role = ?2')
			  ->setParameter(1, $section)
			  ->setParameter(2, $takes_role);
		$query = $builder->getQuery();
		$section_taker_roles = $query->getResult();
		
		$section_takers = [];
		foreach($section_taker_roles as $str){
			$section_takers[] = $str->user;
		}


		# build teams
		$teams_json = json_decode($postData['teams']);
		$teamnames_json = json_decode($postData['teamnames']);
		$teamids_json = json_decode($postData['teamids']);
		
		if(count($teams_json) != count($teamnames_json)){
			return $this->returnForbiddenResponse("The number of teamnames does not equal the number of teams");
		}

		$old_teams = $assignment->teams;
		$mod_teams = new ArrayCollection();
		
		$count = 0;
				
		foreach($teams_json as $team_json){
			
			
			$team_id = $teamids_json[$count];

			// editing a current team
			if($team_id != 0){
				
				$team = $em->find('AppBundle\Entity\Team', $team_id);

				if(!$team || $team->assignment != $assignment){
					return $this->returnForbiddenResponse("Team with id ".$team." does not exist for this assignment");
				}
				
				$team->name = $teamnames_json[$count];				
				$team->users = new ArrayCollection();
				
				foreach($team_json as $user_id){
					
					$temp_user = $em->find('AppBundle\Entity\User', $user_id);

					if(!$temp_user || !in_array($temp_user, $section_takers)){
						return $this->returnForbiddenResponse("User with id ".$user_id." does not take this class");
					}

					$team->users[] = $temp_user;										
				}
				
				if(count($team->users) == 0){
					return $this->returnForbiddenResponse($team->name." did not have any users provided");
				}
				
				//return $this->returnForbiddenResponse("OLD TEAM: ".$team->name);
				$em->persist($team);		
				
				$mod_teams->add($team->id);
			} 
			// new team
			else {
								
				$team = new Team($teamnames_json[$count] , $assignment);
			
				foreach($team_json as $user_id){
										
					$temp_user = $em->find('AppBundle\Entity\User', $user_id);

					if(!$temp_user || !in_array($temp_user, $section_takers)){
						return $this->returnForbiddenResponse("User with id ".$user_id." does not take this class");
					}

					$team->users[] = $temp_user;				
				}
				
				if(count($team->users) == 0){
					return $this->returnForbiddenResponse($team->name." did not have any users provided");
				}
				
				$em->persist($team);			
			}
			
			$count++;
		}
		
		# remove the old teams that no longer exist
		foreach($old_teams as $old){			
			
			if(!$mod_teams->contains($old->id)){				

				$em->remove($old);	
				$em->flush();
			}
		}
			
		$em->persist($assignment);	
		$em->flush();
		
		$url = $this->generateUrl('assignment', ['sectionId' => $assignment->section->id, 'assignmentId' => $assignment->id]);
				
		$response = new Response(json_encode(array('redirect_url' => $url, 'assignment' => $assignment)));
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
