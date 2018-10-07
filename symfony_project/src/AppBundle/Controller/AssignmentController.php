<?php

namespace AppBundle\Controller;

use AppBundle\Constants;

use AppBundle\Entity\User;
use AppBundle\Entity\Course;
use AppBundle\Entity\UserSectionRole;
use AppBundle\Entity\Section;
use AppBundle\Entity\Assignment;
use AppBundle\Entity\Team;
use AppBundle\Entity\Trial;

use AppBundle\Service\AssignmentService;
use AppBundle\Service\ProblemService;
use AppBundle\Service\SectionService;
use AppBundle\Service\SubmissionService;
use AppBundle\Service\TeamService;
use AppBundle\Service\TrialService;
use AppBundle\Service\UserSectionRoleService;
use AppBundle\Service\UserService;

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
	private $assignmentService;
	private $logger;
	private $problemService;
	private $teamService;
	private $sectionService;
	private $submissionService;
	private $userSectionRoleService;
	private $userService;

	public function __construct(AssignmentService $assignmentService,
	                            LoggerInterface $logger,
								ProblemService $problemService,
								SectionService $sectionService,
								SubmissionService $submissionService,
								TeamService $teamService,
								TrialService $trialService,
								UserSectionRoleService $userSectionRoleService,
		                        UserService $userService) {
		$this->assignmentService = $assignmentService;
		$this->logger = $logger;
		$this->problemService = $problemService;
		$this->sectionService = $sectionService;
		$this->submissionService = $submissionService;
		$this->teamService = $teamService;
		$this->trialService = $trialService;
		$this->userSectionRoleService = $userSectionRoleService;
		$this->userService = $userService;
	}

	public function assignmentAction($sectionId, $assignmentId, $problemId) {
		$entityManager = $this->getDoctrine()->getManager();
		
		$user = $this->userService->getCurrentUser();
		if (!get_class($user)) {
			$this->logError("USER DOES NOT EXteamServiceST");
			return $this->redirectToRoute("usteamServicer_login");
	  }
		
		// get the section
		if (!isset($sectionId) || !($sectionId > 0)){
			$this->logError("SECTION ID WAS NOT PROVIDED OR FORMATTED PROPERLY");
			return $this->redirectToRoute("homepage");
		}
		
		$section = $this->sectionService->getSectionById($entityManager, $sectionId);
		if (!$section) {
			$this->logError("SECTION DOES NOT EXIST");
			return $this->redirectToRoute("homepage");
		}
		
		// REDIRECT TO CONTEST IF NEED BE
		if ($section->course->is_contest) {
			if (!isset($problemId)) {
				return $this->redirectToRoute("contest", 
				[
					"contestId" => $sectionId,
					"roundId" => $assignmentId
				]);
			} else {
				return $this->redirectToRoute("contest_problem", 
				[
					"contestId" => $sectionId, 
					"roundId" => $assignmentId, 
					"problemId" => $problemId
				]);
			}
		}
		
		// get the assignment
		if (!isset($assignmentId) || !($assignmentId > 0)) {
			$this->logError("ASSIGNMENT ID WAS NOT PROVIDED OR FORMATTED PROPERLY");
			return $this->redirectToRoute("homepage");
		}
		
		$assignment = $this->assignmentService->getAssignmentById($entityManager, $assignmentId);
		if (!$assignment) {
			$this->logError("ASSIGNMENT DOES NOT EXIST");
			return $this->redirectToRoute("homepage");
		}
				
		if ($problemId == 0) {
			$problemId = $assignment->problems[0]->id;
		}
		
		$languages = [];
		$aceModes = [];
		$fileTypes = [];
		if ($problemId != null) {	
			if (!($problemId > 0)) {
				$this->logError("PROBLEM ID WAS NOT FORMATTED PROPERLY");
				return $this->redirectToRoute("homepage");
			}
			
			$problem = $this->problemService->getProblemById($entityManager, $problemId);
			if (!$problem || $problem->assignment != $assignment) {
				$this->logError("PROBLEM DOES NOT EXIST");
				return $this->redirectToRoute("homepage");
			}
			
			$problemLanguages = $problem->problem_languages;
			foreach ($problemLanguages as $problemLanguage) {
				$languages[] = $problemLanguage->language;
				$aceModes[$problemLanguage->language->name] = $problemLanguage->language->ace_mode;
				$fileTypes[str_replace(".", "", $problemLanguage->language->filetype)] = $problemLanguage->language->name;
			}
		}

		// get the usersectionrole
		$userSectionRole = $this->userSectionRoleService->getUserSectionRolesForAssignment($entityManager, $problem->assignment->section);
		$grader = new Grader($entityManager);
		
		// figure out how many attempts they have left
		$totalAttempts = $problem->total_attempts;
		$attemptsRemaining = -1;
		if ($totalAttempts != 0 && !$grader->isTeaching($user, $assignment->section) && !$grader->isJudging($user, $assignment->section)) {
			$attemptsRemaining = max($totalAttempts - $grader->getNumTotalAttempts($user, $problem), 0);
		}
		
		// get the team
		$team = $this->teamService->getTeam($entityManager, $user, $assignment);

		$teamOrUser = $user;
		$whereClause = "s.user = ?1";
		if (isset($team)) {
			$teamOrUser = $team;
			$whereClause = "s.team = ?1";
		}

		// get the best submission so far
		$bestSubmission = $this->submissionService->getBestSubmissionForAssignment($entityManager, $teamOrUser, $whereClause, $problem);
		// get the code from the last submissions
		$allSubmissions = $this->submissionService->getAllSubmissionsForAssignment($entityManager, $teamOrUser, $whereClause, $problem);
		
		// get the user's trial for this problem
		$trial = $this->trialService->getTrialForAssignment($entityManager, $user, $problem);
		
		$submissionId = $_GET["submissionId"];
		if (isset($submissionId) && $submissionId > 0) {
			$submission = $this->submissionService->getSubmissionById($submissionId);
			
			if ($submission->user != $user || $submission->problem != $problem) {
				$this->logError($user->name." is not allowed to edit this submission on this problem");
				$this->redirectToRoute("homepage");
			}
						
			if (!$trial) {
				$trial = $this->createTrial($user, $problem, true);
			}
			
			$trial->file = $submission->submitted_file;						
			
			$trial->language = $submission->language;	
			$trial->filename = $submission->filename;
			$trial->main_class = $submission->main_class_name;
			$trial->package_name = $submission->package_name;
			$trial->last_edit_time = new \DateTime("now");
			
			$entityManager->flush();
			
			return $this->redirectToRoute("assignment", 
			[
				"sectionId" => $section->id, 
				"assignmentId" => $assignment->id, 
				"problemId" => $problem->id
			]);
		}
		
		// get all userSectionRoles
		$userSectionRoles = $this->userSectionRoleService->getUserSectionRolesOfSection($entityManager, $section);

		$section_takers = [];
		$section_teachers = [];
		$section_helpers = [];
		$section_judges = [];

		foreach ($userSectionRoles as $usr) {
			$user = $usr->user;
			$roleName = $usr->role->role_name;
			switch ($roleName) {
				case Constants::HELPS_ROLE:
					$section_helpers[] = $usr->user;
					break;
				case Constants::JUDGES_ROLE:
					$section_judges[] = $usr->user;
					break;
				case Constants::TAKES_ROLE:
					$section_takers[] = $usr->user;
					break;
				case Constants::TEACHES_ROLE:
					$section_teachers[] = $usr->user;
					break;
				default:
					$this->logError("ROLE ".$roleName." DOES NOT EXIST");
					return $this->redirectToRoute("homepage");
			}
		}
		
		return $this->render("assignment/index.html.twig", [
			"ace_modes" => $aceModes,
			"all_submissions" => $allSubmissions,
			"assignment" => $assignment,
			"attempts_remaining" => $attemptsRemaining,
			"best_submission" => $bestSubmission,
			"filetypes" => $fileTypes,
			"grader" => $grader,
			"grades" => $grades,
			"languages" => $languages,
			"problem" => $problem,
			"section" => $assignment->section,
			"section_takers" => $section_takers,
			"team" => $team,
			"trial" => $trial,
			"user" => $user,
			"user_impersonators" => $section_takers,
			"usersectionrole" => $userSectionRole
		]);

	}

    public function editAction($sectionId, $assignmentId) {

		$entityManager = $this->getDoctrine()->getManager();
		
		if(!isset($sectionId) || !($sectionId > 0)){
			die("SECTION ID WAS NOT PROVIDED OR NOT FORMATTED PROPERLY");
		}

		$section = $entityManager->find("AppBundle\Entity\Section", $sectionId);	
		if(!$section){
			die("SECTION DOES NOT EXIST");
		}
		
		if($section->course->is_contest){
			return $this->returnForbiddenResponse("contest_edit", ["contestId" => $sectionId]);
		}
		
		$user = $this->get("security.token_storage")->getToken()->getUser();  	  
		if(!get_class($user)){
			die("USER DOES NOT EXIST!");		  
		}
		
		// validate the user
		$grader = new Grader($entityManager);
		if(!$user->hasRole("ROLE_SUPER") && !$user->hasRole("ROLE_ADMIN") && !$grader->isTeaching($user, $section) && !$grader->isJudging($user, $section)){
			die("YOU ARE NOT ALLOWED TO EDIT THIS ASSIGNMENT");			
		}		
		
		if($assignmentId != 0){
			
			if(!($assignmentId > 0)){
				die("ASSIGNMENT ID WAS NOT FORMATTED PROPERLY");
			}
									
			$assignment = $entityManager->find("AppBundle\Entity\Assignment", $assignmentId);
			
			if(!$assignment || $section != $assignment->section){
				die("Assignment does not exist or does not belong to given section");
			}
		}
				
		// get all the users taking the course
		$takes_role = $entityManager->getRepository("AppBundle\Entity\Role")->findOneBy(array("role_name" => "Takes"));
		$builder = $entityManager->createQueryBuilder();
		$builder->select("u")
			  ->from("AppBundle\Entity\UserSectionRole", "u")
			  ->where("u.section = ?1")
			  ->andWhere("u.role = ?2")
			  ->setParameter(1, $section)
			  ->setParameter(2, $takes_role);
		$query = $builder->getQuery();
		$section_taker_roles = $query->getResult();

		$students = [];
		foreach($section_taker_roles as $usr){
			$student = [];
			
			$student["id"] = $usr->user->id;
			$student["name"] = $usr->user->getFirstName()." ".$usr->user->getLastName();
			
			$students[] = $student;
		}

		return $this->render("assignment/edit.html.twig", [
			"assignment" => $assignment,
			"section" => $section,
			"edit" => true,
			"students" => $students,
		]);
    }

    public function deleteAction($sectionId, $assignmentId){
	
		$entityManager = $this->getDoctrine()->getManager();
		
		// get the assignment
		if(!isset($assignmentId) || !($assigmentId > 0)){
			die("ASSIGNMENT ID WAS NOT PROVIDED OR FORMATTED PROPERLY");
		}
		
		$assignment = $entityManager->find("AppBundle\Entity\Assignment", $assignmentId);	  
		if(!$assignment){
			die("ASSIGNMENT DOES NOT EXIST");
		}
		
		$user = $this->get("security.token_storage")->getToken()->getUser();
		if(!$user){
			die("USER DOES NOT EXIST");
		}
		
		// validate the user
		$grader = new Grader($entityManager);
		if(!$user->hasRole("ROLE_SUPER") && !$user->hasRole("ROLE_ADMIN") && !$grader->isTeaching($user, $assignment->section) && !$grader->isJudging($user, $assignment->section)){
			die("YOU ARE NOT ALLOWED TO DELETE THIS ASSIGNMENT");			
		}
		
		$entityManager->remove($assignment);
		$entityManager->flush();
		
		return $this->redirectToRoute("section", ["sectionId" => $assignment->section->id]);
	}
	
	public function modifyPostAction(Request $request) {
		
		$entityManager = $this->getDoctrine()->getManager();
				
		// validate the current user
		$user = $this->get("security.token_storage")->getToken()->getUser();
		if(!$user){			
			return $this->returnForbiddenResponse("You are not a user.");
		}
		
		// see which fields were included
		$postData = $request->request->all();
		
		// get the current section
		// get the assignment
		if(!isset($postData["section"]) || !($postData["section"] > 0)){
			return $this->returnForbiddenResponse("SECTION ID WAS NOT PROVIDED OR FORMATTED PROPERLY");
		}
		
		$section = $entityManager->find("AppBundle\Entity\Section", $postData["section"]);		
		if(!$section){
			return $this->returnForbiddenResponse("Section ".$postData["section"]." does not exist");
		}
		
		// only super users/admins/teacher can make/edit an assignment
		$grader = new Grader($entityManager);		
		if(!$user->hasRole("ROLE_SUPER") && !$user->hasRole("ROLE_ADMIN") && !$grader->isTeaching($user, $section) && !$grader->isJudging($user, $section)){			
			return $this->returnForbiddenResponse("You do not have permission to make an assignment.");
		}		
		
		// check mandatory fields
		if(!isset($postData["name"]) || !isset($postData["open_time"]) || !isset($postData["close_time"]) || !isset($postData["teams"]) || !isset($postData["teamnames"])){
			return $this->returnForbiddenResponse("Not every required field is provided.");			
		} else {
			
			// validate the weight if there is one
			if(	is_numeric(trim($postData["weight"])) && 
				((int)trim($postData["weight"]) < 0 || $postData["weight"] % 1 != 0)){
					
				return $this->returnForbiddenResponse("The provided weight ".$postData["weight"]." is not permitted.");
			}

			// validate the penalty if there is one
			if(is_numeric(trim($postData["penalty"])) && 
				((float)trim($postData["penalty"]) > 1.0 || (float)trim($postData["penalty"]) < 0.0)){
					
				return $this->returnForbiddenResponse("The provided penalty ".$postData["penalty"]." is not permitted.");
			}			
		}		
		
		// create new assignment
		if($postData["assignment"] == 0){
			$assignment = new Assignment();		
			$entityManager->persist($assignment);
			
		} else {
			
			if(!isset($postData["assignment"]) || !($postData["assignment"] > 0)){
				die("ASSIGNMENT ID WAS NOT PROVIDED OR FORMATTED PROPERLY");
			}
			
			$assignment = $entityManager->find("AppBundle\Entity\Assignment", $postData["assignment"]);
			
			if(!$assignment || $section != $assignment->section){
				return $this->returnForbiddenResponse("Assignment ".$postData["assignment"]." does not exist for the given section.");
			}			
		}
		
		// set the necessary fields
		$assignment->name = trim($postData["name"]);
		$assignment->description = trim($postData["description"]);
		$assignment->section = $section;
		
		// set the times		
		$openTime = DateTime::createFromFormat("m/d/Y H:i:s", $postData["open_time"].":00");
		$closeTime = DateTime::createFromFormat("m/d/Y H:i:s", $postData["close_time"].":00");
		
		if(!isset($openTime) || $openTime->format("m/d/Y H:i") != $postData["open_time"]){
			return $this->returnForbiddenResponse("Provided opening time ".$postData["open_time"]." is not valid.");
		}
		
		if(!isset($closeTime) || $closeTime->format("m/d/Y H:i") != $postData["close_time"]){
			return $this->returnForbiddenResponse("Provided closing time ".$postData["close_time"]." is not valid.");
		}
		
		if(isset($postData["cutoff_time"]) && $postData["cutoff_time"] != ""){
			
			$cutoffTime = DateTime::createFromFormat("m/d/Y H:i:s", $postData["cutoff_time"].":00");
			
			if(!isset($cutoffTime) || $cutoffTime->format("m/d/Y H:i") != $postData["cutoff_time"]){
				return $this->returnForbiddenResponse("Provided cutoff time ".$postData["cutoff_time"]." is not valid.");
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
		
		// set the weight
		if(isset($postData["weight"])){
			$assignment->weight = (int)trim($postData["weight"]);
		} else {
			$assignment->weight = 1;
		}				
				
		// set extra credit
		if(isset($postData["is_extra_credit"]) && $postData["is_extra_credit"] == "true"){
			$assignment->is_extra_credit = true;
		} else {			
			$assignment->is_extra_credit = false;
		}
		
		// set grading penalty
		$penalty = (float)trim($postData["penalty"]);		
		$assignment->penalty_per_day = $penalty;		
	
		// get all the users taking the course
		$takes_role = $entityManager->getRepository("AppBundle\Entity\Role")->findOneBy(array("role_name" => "Takes"));
		$builder = $entityManager->createQueryBuilder();
		$builder->select("u")
			  ->from("AppBundle\Entity\UserSectionRole", "u")
			  ->where("u.section = ?1")
			  ->andWhere("u.role = ?2")
			  ->setParameter(1, $section)
			  ->setParameter(2, $takes_role);
		$query = $builder->getQuery();
		$section_taker_roles = $query->getResult();
		
		$section_takers = [];
		foreach($section_taker_roles as $str){
			$section_takers[] = $str->user;
		}


		// build teams
		$teams_json = json_decode($postData["teams"]);
		$teamnames_json = json_decode($postData["teamnames"]);
		$teamids_json = json_decode($postData["teamids"]);
		
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
				
				$team = $entityManager->find("AppBundle\Entity\Team", $team_id);

				if(!$team || $team->assignment != $assignment){
					return $this->returnForbiddenResponse("Team with id ".$team." does not exist for this assignment");
				}
				
				$team->name = $teamnames_json[$count];				
				$team->users = new ArrayCollection();
				
				foreach($team_json as $user_id){
					
					$temp_user = $entityManager->find("AppBundle\Entity\User", $user_id);

					if(!$temp_user || !in_array($temp_user, $section_takers)){
						return $this->returnForbiddenResponse("User with id ".$user_id." does not take this class");
					}

					$team->users[] = $temp_user;										
				}
				
				if(count($team->users) == 0){
					return $this->returnForbiddenResponse($team->name." did not have any users provided");
				}
				
				//return $this->returnForbiddenResponse("OLD TEAM: ".$team->name);
				$entityManager->persist($team);		
				
				$mod_teams->add($team->id);
			} 
			// new team
			else {
								
				$team = new Team($teamnames_json[$count] , $assignment);
			
				foreach($team_json as $user_id){
										
					$temp_user = $entityManager->find("AppBundle\Entity\User", $user_id);

					if(!$temp_user || !in_array($temp_user, $section_takers)){
						return $this->returnForbiddenResponse("User with id ".$user_id." does not take this class");
					}

					$team->users[] = $temp_user;				
				}
				
				if(count($team->users) == 0){
					return $this->returnForbiddenResponse($team->name." did not have any users provided");
				}
				
				$entityManager->persist($team);			
			}
			
			$count++;
		}
		
		// remove the old teams that no longer exist
		foreach($old_teams as $old){			
			
			if(!$mod_teams->contains($old->id)){				

				$entityManager->remove($old);	
				$entityManager->flush();
			}
		}
			
		$entityManager->persist($assignment);	
		$entityManager->flush();
		
		$url = $this->generateUrl("assignment", ["sectionId" => $assignment->section->id, "assignmentId" => $assignment->id]);
				
		$response = new Response(json_encode(array("redirect_url" => $url, "assignment" => $assignment)));
		$response->headers->set("Content-Type", "application/json");
		$response->setStatusCode(Response::HTTP_OK);
		
		return $response;
	}
	
	public function clearSubmissionsAction(Request $request){

		$entityManager = $this->getDoctrine()->getManager();
				
		// validate the current user
		$user = $this->get("security.token_storage")->getToken()->getUser();
		if(!$user){			
			return $this->returnForbiddenResponse("You are not a user.");
		}
		
		// see which fields were included
		$postData = $request->request->all();
		
		// get the current section
		// get the assignment
		if(!isset($postData["assignment"])){
			return $this->returnForbiddenResponse("Assignment ID was not provided");
		}
		
		$assignment = $entityManager->find("AppBundle\Entity\Assignment", $postData["assignment"]);		
		if(!$assignment){
			return $this->returnForbiddenResponse("Section ".$postData["assignment"]." does not exist");
		}

		$section = $assignment->section;
		
		// only super users/admins/teacher can make/edit an assignment
		$grader = new Grader($entityManager);		
		if( !($user->hasRole("ROLE_SUPER") || $user->hasRole("ROLE_ADMIN") || $grader->isTeaching($user, $section) || $grader->isJudging($user, $section)) ){			
			return $this->returnForbiddenResponse("You do not have permission to do this.");
		}

		// delete all submission but keep all of the trials
		$qb = $entityManager->createQueryBuilder();
		$qb->delete("AppBundle\Entity\Submission", "s");
		$qb->where("s.problem IN (?1)");
		$qb->setParameter(1, $assignment->problems->toArray());

		$result = $qb->getQuery()->getResult();

		$entityManager->flush();

		$response = new Response(json_encode([
			"result" => $result,
		]));

		$response->headers->set("Content-Type", "application/json");
		$response->setStatusCode(Response::HTTP_OK);
		
		return $response;				
	}

	public function clearTrialsAction(Request $request){

		$entityManager = $this->getDoctrine()->getManager();
				
		// validate the current user
		$user = $this->get("security.token_storage")->getToken()->getUser();
		if(!$user){			
			return $this->returnForbiddenResponse("You are not a user.");
		}
		
		// see which fields were included
		$postData = $request->request->all();
		
		// get the current section
		// get the assignment
		if(!isset($postData["assignment"])){
			return $this->returnForbiddenResponse("Assignment ID was not provided");
		}
		
		$assignment = $entityManager->find("AppBundle\Entity\Assignment", $postData["assignment"]);		
		if(!$assignment){
			return $this->returnForbiddenResponse("Section ".$postData["assignment"]." does not exist");
		}

		$section = $assignment->section;
		
		// only super users/admins/teacher can make/edit an assignment
		$grader = new Grader($entityManager);		
		if( !($user->hasRole("ROLE_SUPER") || $user->hasRole("ROLE_ADMIN") || $grader->isTeaching($user, $section) || $grader->isJudging($user, $section)) ){			
			return $this->returnForbiddenResponse("You do not have permission to do this.");
		}

		// delete all submission but keep all of the trials
		$qb = $entityManager->createQueryBuilder();
		$qb->delete("AppBundle\Entity\Trial", "t");
		$qb->where("t.problem IN (?1)");
		$qb->setParameter(1, $assignment->problems->toArray());

		$result = $qb->getQuery()->getResult();

		$entityManager->flush();

		$response = new Response(json_encode([
			"result" => $result,
		]));

		$response->headers->set("Content-Type", "application/json");
		$response->setStatusCode(Response::HTTP_OK);
		
		return $response;				
	}
	
	private function logError($message) {
		$this->logger->error("AssignmentController: ".$message);
	}
	
	private function returnForbiddenResponse($message){		
		$response = new Response($message);
		$response->setStatusCode(Response::HTTP_FORBIDDEN);
		return $response;
	}
}

?>
