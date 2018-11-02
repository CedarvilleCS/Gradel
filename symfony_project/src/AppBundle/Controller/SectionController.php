<?php

namespace AppBundle\Controller;

use \DateTime;
use \DateInterval;

use AppBundle\Constants;

use AppBundle\Entity\Assignment;
use AppBundle\Entity\Course;
use AppBundle\Entity\Role;
use AppBundle\Entity\Section;
use AppBundle\Entity\Submission;
use AppBundle\Entity\User;
use AppBundle\Entity\UserSectionRole;

use AppBundle\Service\AssignmentService;
use AppBundle\Service\CourseService;
use AppBundle\Service\GraderService;
use AppBundle\Service\RoleService;
use AppBundle\Service\SectionService;
use AppBundle\Service\SubmissionService;
use AppBundle\Service\UserSectionRoleService;
use AppBundle\Service\UserService;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Doctrine\ORM\Tools\Pagination\Paginator;

use Psr\Log\LoggerInterface;

class SectionController extends Controller {
	private $assignmentService;
	private $courseService;
	private $logger;
	private $graderService;
	private $roleService;
	private $sectionService;
	private $submissionService;
	private $userSectionRoleService;
	private $userService;

	public function __construct(AssignmentService $assignmentService,
								CourseService $courseService,
		                        LoggerInterface $logger,
								GraderService $graderService,
								RoleService $roleService,
								SectionService $sectionService,
								SubmissionService $submissionService,
								UserSectionRoleService $userSectionRoleService,
								UserService $userService) {
		$this->assignmentService = $assignmentService;
		$this->courseService = $courseService;
		$this->logger = $logger;
		$this->graderService = $graderService;
		$this->roleService = $roleService;
		$this->sectionService = $sectionService;
		$this->submissionService = $submissionService;
		$this->userSectionRoleService = $userSectionRoleService;
		$this->userService = $userService;
	}

    public function sectionAction($sectionId) {
		$user = $this->userService->getCurrentUser();
		if (!get_class($user)) {
			return $this->returnForbiddenResponse("USER DOES NOT EXIST");
		}

		$section = $this->sectionService->getSectionById($sectionId);
		if (!$section) {
			return $this->returnForbiddenResponse("SECTION ".$sectionId." DOES NOT EXIST!");
		}
		
		/* Redirect to contest if need be */
		if ($section->course->is_contest) {
			return $this->redirectToRoute("contest", ["contestId" => $section->id]);
		}
		

		/* Get all assignments */
		$assignments = $this->assignmentService->getAssignmentsBySection($section);

		/* Get all users */
		$userSectionRoles = $this->userSectionRoleService->getUserSectionRolesOfSection($section);

		$sectionTakers = [];
		$sectionTeachers = [];
		$sectionHelpers = [];
		$sectionJudges = [];

		foreach ($userSectionRoles as $usr) {
			$user = $usr->user;
			$roleName = $usr->role->role_name;
			switch ($roleName) {
				case Constants::HELPS_ROLE:
					$sectionHelpers[] = $usr->user;
					break;
				case Constants::JUDGES_ROLE:
					$sectionJudges[] = $usr->user;
					break;
				case Constants::TAKES_ROLE:
					$sectionTakers[] = $usr->user;
					break;
				case Constants::TEACHES_ROLE:
					$sectionTeachers[] = $usr->user;
					break;
				default:
					return $this->returnForbiddenResponse("ROLE ".$roleName." DOES NOT EXIST ON USER ".$user->getFullName());
			}
		}
		
		/* Get future assignments */
		$futureAssignments = $this->assignmentService->getAssignmentsSortedByDueDateForSection($section);
		
		/* Gather submissions */
		/* Get all of the problems to get all of the submissions */
		$allProblems = [];
		foreach ($section->assignments as $sectionAssignments) {
			foreach ($sectionAssignments->problems as $problem) {
				$allProblems[] = $problem;
			}
		}
		
		/* Get assignment grades for the student */
		$grades = [];
		$assignmentProblemSubmissions = [];
		$team = [];
		foreach ($sectionTakers as $sectionTaker) {	
			$correctSubmissions = [];
			
			$grades[$sectionTaker->id] = $this->graderService->getAllAssignmentGrades($sectionTaker, $section);
			
			foreach ($assignments as $assignment) {
				$assignmentProblems = $assignment->problems;
				$team = $this->graderService->getTeam($sectionTaker, $assignment);

				if ($team) {
					foreach ($assignmentProblems as $assignmentProblem) {
						$bestSubmission = $this->submissionService->getBestSubmissionForTeam($assignmentProblem, $team);
						if ($bestSubmission) {
							$correctSubmissions[$assignment->id][$assignmentProblem->id] = $bestSubmission->id;
						}
					}
				}
			}
			$assignmentProblemSubmissions[$sectionTaker->id] = $correctSubmissions;
		}


		/* Get the users most recent submissions (top 15) */
		$entityManager = $this->getDoctrine()->getManager();		
		$recentSubmissions = $this->submissionService->getRecentResultsForUser($user);

		/* Array of arrays that contain a main text and a subtext that will be used for autocompleting searches
		   ["Timothy Smith", "timothyglensmith@cedarville.edu"]
		   ["Get the Sum", "Homework #2"]
		   ["Wrong Answer", "Incorrect"] */
		$suggestions = [];

		/* Get the users */
		foreach ($sectionTakers as $sectionTaker) {
			$suggestions[] = [$sectionTaker->getFullName(), $sectionTaker->getEmail()];
		}

		/* Get the teachers */
		foreach ($sectionTeachers as $sectionTeacher) {
			$suggestions[] = [$sectionTeacher->getFullName(), $sectionTeacher->getEmail()];
		}

		/* Get the helpers */
		foreach ($sectionHelpers as $sectionHelper) {
			$suggestions[] = [$sectionHelper->getFullName(), $sectionHelper->getEmail()];
		}

		/* Get the problems */
		foreach ($allProblems as $assignmentProblem) {
			$suggestions[] = [$assignmentProblem->name, $assignmentProblem->assignment->name];			
		}

		/* Get the assignments and teams */
		foreach ($section->assignments as $sectionAssignment) {
			$suggestions[] = [$sectionAssignment->name, ""];			

			foreach ($sectionAssignment->teams as $assignmentTeam) {
				if ($assignmentTeam->users->count() > 1) {
					$suggestions[] = [$assignmentTeam->name, ""];
				}
			}
		}

		/* Get the correct types */
		$suggestions[] = ["Correct", ""];
		$suggestions[] = ["Incorrect", ""];
		$suggestions[] = ["Wrong Answer", "Incorrect"];
		$suggestions[] = ["Runtime Error", "Incorrect"];
		$suggestions[] = ["Time Limit Error", "Incorrect"];
		$suggestions[] = ["Compile Error", "Incorrect"];
				
		return $this->render("section/index.html.twig", [
			"future_assigs" => $futureAssignments,
			"grader" => $this->graderService,
			"grades" => $grades,
			"search_suggestions" => $suggestions,
			"section" => $section,
			"section_helpers" => $sectionHelpers,
			"section_takers" => $sectionTakers,
			"section_teachers" => $sectionTeachers,
			"submissions" => $recentSubmissions,
			"team" => $team,
			"user" => $user,
			"user_assig_prob_sub" => $assignmentProblemSubmissions,
			"user_impersonators" => $sectionTakers
		]);
	}

    public function editSectionAction($sectionId) {
		$entityManager = $this->getDoctrine()->getManager();

		$user = $this->userService->getCurrentUser();
		if (!get_class($user)) {
			return $this->returnForbiddenResponse("USER DOES NOT EXIST");
		}

		if ($sectionId != 0) {
			if (!isset($sectionId) || !($sectionId > 0)) {
				return $this->returnForbiddenResponse("SECTION ID WAS NOT PROVIDED OR NOT FORMATTED PROPERLY");
			}

			$section = $this->sectionService->getSectionById($sectionId);
			if (!$section) {
				return $this->returnForbiddenResponse("SECTION ".$sectionId." DOES NOT EXIST");
			}

			/* Redirect to contest if need be */
			if ($section->course->is_contest) {
				return $this->redirectToRoute("contest_edit", ["contestId" => $section->id]);
			}
			
			$sectionTakerRoles = [];
			$sectionTeacherRoles = [];
	
			$teachesRole = $this->roleService->getRoleByRoleName(Constants::TEACHES_ROLE);
			$takesRole = $this->roleService->getRoleByRoleName(Constants::TAKES_ROLE);
			
			foreach ($section->user_roles as $sectionUserRole) {
				if ($sectionUserRole->role == $takesRole) {
					$sectionTakerRoles[] = $sectionUserRole;
				} else if ($sectionUserRole->role == $teachesRole) {
					$sectionTeacherRoles[] = $sectionUserRole;
				}
			}
		}
		
		$courses = $this->courseService->getNonDeletedCourses();
		$users = $this->userService->getAllUsers();
		$instructors = [];

		foreach ($users as $potentialTeacher) {
			if ($potentialTeacher->hasRole(Constants::ADMIN_ROLE) or $potentialTeacher->hasRole(Constants::SUPER_ROLE)) {
				$instructors[] = $potentialTeacher;
			}
		}

		return $this->render("section/edit.html.twig", [
			"courses" => $courses,
			"users" => $users,
			"instructors" => $instructors,
			"section" => $section,
			"section_taker_roles" => $sectionTakerRoles,
			"section_teacher_roles" => $sectionTeacherRoles
		]);
    }

	public function cloneSectionAction($sectionId, $name, $semester, $year, $numberOfClones) {
		$user = $this->userService->getCurrentUser();
		if (!get_class($user)) {
			return $this->returnForbiddenResponse("USER DOES NOT EXIST");
		}

		$section = $this->sectionService->getSectionById($sectionId);
		if (!$section) {
			return $this->returnForbiddenResponse("SECTION ".$sectionId." DOES NOT EXIST");
		}

		for ($i = 1; $i <= $numberOfClones; $i++) {
			$newSection = clone $section;
			$newSection->semester = $semester;
			$newSection->name = $name."-".str_pad($i, 2, "0", STR_PAD_LEFT);
			$newSection->year = $year;
			$this->sectionService->insertSection($newSection);
		}
		return $this->redirectToRoute("section_edit",
		[
			"sectionId" => $newSection->id
		]);
	}

	public function deleteSectionAction($sectionId){

		$entityManager = $this->getDoctrine()->getManager();

		# get the section
		if(!isset($sectionId) || !($sectionId > 0)){
			return $this->returnForbiddenResponse("SECTION ID WAS NOT PROVIDED OR NOT FORMATTED PROPERLY");
		}
		$section = $entityManager->find("AppBundle\Entity\Section", $sectionId);

		if(!$section){
			return $this->returnForbiddenResponse("SECTION DOES NOT EXIST");
		}

		$user = $this->get("security.token_storage")->getToken()->getUser();
		if(!$user){
			return $this->returnForbiddenResponse("USER DOES NOT EXIST");
		}

		# validate the user
		if(!$user->hasRole("ROLE_SUPER") && !$user->hasRole("ROLE_ADMIN")){
			return $this->returnForbiddenResponse("YOU ARE NOT ALLOWED TO DELETE THIS SECTION");

		}

		$section->is_deleted = !$section->is_deleted;
		$entityManager->flush();

		return $this->redirectToRoute("homepage");
	}

	public function modifyPostAction(Request $request){

		$entityManager = $this->getDoctrine()->getManager();

		# validate the current user
		$user = $this->get("security.token_storage")->getToken()->getUser();
		if(!$user){
			return $this->returnForbiddenResponse("You are not a user.");
		}
		
		# see which fields were included
		$postData = $request->request->all();
		
		# check mandatory fields
		if(!isset($postData["name"]) || trim($postData["name"]) == "" || !isset($postData["course"]) || !isset($postData["semester"]) || !isset($postData["year"])){
			return $this->returnForbiddenResponse("Not every required field is provided.");
		} else {
			
			# validate the year
			if(!is_numeric(trim($postData["year"]))){
				return $this->returnForbiddenResponse($postData["year"]." is not a valid year");
			}
			
			# validate the semester
			if(trim($postData["semester"]) != "Fall" && trim($postData["semester"]) != "Spring" && trim($postData["semester"]) != "Summer"){
				return $this->returnForbiddenResponse($postData["semester"]." is not a valid semester");
			}
		}
		
		// $graderService = new Grader($entityManager);
		# create new section
		if($postData["section"] == 0){
			
			# only super users and admins can make/edit a section
			if(!$user->hasRole("ROLE_SUPER") && !$user->hasRole("ROLE_ADMIN")){
				return $this->returnForbiddenResponse("You do not have permission to make a section.");
			}
			
			$section = new Section();
		} else if(isset($postData["section"])) {
			
			if(!isset($postData["section"]) || !($postData["section"] > 0)){
				return $this->returnForbiddenResponse("SECTION ID WAS NOT PROVIDED OR NOT FORMATTED PROPERLY");
			}
			$section = $entityManager->find("AppBundle\Entity\Section", $postData["section"]);
			
			if(!$section){
				return $this->returnForbiddenResponse("Section ".$postData["section"]." does not exist");
			}
			
			# only super users and admins can make/edit a section
			if(! ($user->hasRole("ROLE_SUPER") || $user->hasRole("ROLE_ADMIN") || $this->graderService->isTeaching($user, $section)) ){
				return $this->returnForbiddenResponse("You do not have permission to edit this section.");
			}			
			
		} else {
			return $this->returnForbiddenResponse("section not provided");
		}
		
		# get the course
		if(!isset($postData["course"]) || !($postData["course"] > 0)){
			return $this->returnForbiddenResponse("COURSE ID WAS NOT PROVIDED OR NOT FORMATTED PROPERLY");
		}
		
		$course = $entityManager->find("AppBundle\Entity\Course", $postData["course"]);
		if(!$course){
			return $this->returnForbiddenResponse("Course provided does not exist.");
		}
		
		# set the necessary fields
		$section->name = trim($postData["name"]);
		$section->course = $course;
		$section->semester = $postData["semester"];
		$section->year = (int)trim($postData["year"]);
		
		# see if the dates were provided or if we will do them automatically
		$dates = $this->getDateTime($postData["semester"], $postData["year"]);
		if(isset($postData["start_time"]) && $postData["start_time"] != ""){
			$customStartTime = DateTime::createFromFormat("m/d/Y H:i:s", $postData["start_time"]." 00:00:00");
			
			if(!$customStartTime || $customStartTime->format("m/d/Y") != $postData["start_time"]){
				return $this->returnForbiddenResponse("Provided invalid start time ". $postData["start_time"]);
			} else {
				$section->start_time = $customStartTime;
			}
			
		} else {
			$section->start_time = $dates[0];
		}
		
		if(isset($postData["end_time"]) && $postData["end_time"] != ""){
			$customEndTime = DateTime::createFromFormat("m/d/Y H:i:s", $postData["end_time"]." 23:59:59");
			
			if(!$customEndTime || $customEndTime->format("m/d/Y") != $postData["end_time"]){
				return $this->returnForbiddenResponse("Provided invalid end time ". $postData["end_time"]);
			} else {
				$section->end_time = $customEndTime;
			}
			
		} else {
			$section->end_time = $dates[1];
		}
		
		# validate that the end time is after the start time
		if($section->end_time <= $section->start_time){
			return $this->returnForbiddenResponse("The end time must be after the start time for the section");
		}
		
		# default these to false
		$section->is_deleted = false;
		$section->is_public = false;
		
		$entityManager->persist($section);
		
		# validate the students csv
		$students = array_unique(json_decode($postData["students"]));
		
		foreach ($students as $student) {
			if (!filter_var($student, FILTER_VALIDATE_EMAIL)) {
				return $this->returnForbiddenResponse("Provided student email address ".$student." is not valid");
			}
			
			if (in_array($student, $teachers)) {
				return $this->returnForbiddenResponse("Cannot add " . $student . " as a student. He/she already teaches this section!");
			}
		}
		
		# vallidate teacher csv
		$teachers = array_unique(json_decode($postData["teachers"]));
		
		foreach ($teachers as $sectionTeacher){
			
			if(!filter_var($sectionTeacher, FILTER_VALIDATE_EMAIL)) {
				return $this->returnForbiddenResponse("Provided teacher email address ".$sectionTeacher." is not valid");
			}
			
			if (in_array($sectionTeacher, $students)) {
				return $this->returnForbiddenResponse("Cannot add " .$sectionTeacher . "as a student. He/she already teaches this section!");
			}
		}
		
		$oldUsers = [];
		
		if($postData["section"] == 0 && count(json_decode($postData["teachers"])) == 0){
			
			# add the current user as a role
			$role = $entityManager->getRepository("AppBundle\Entity\Role")->findOneBy(array("role_name" => "Teaches"));
			$usr = new UserSectionRole($user, $section, $role);
			$entityManager->persist($usr);
			
		} else if($postData["section"] != 0){
			
			foreach($section->user_roles as $ur){
				$entityManager->remove($ur);
				
				$oldUsers[$ur->user->id] = $ur->user;
			}
			
			$entityManager->flush();
		}
		
		# add students from the students array
		
		$takesRole = $entityManager->getRepository("AppBundle\Entity\Role")->findOneBy(array("role_name" => "Takes"));
		foreach ($students as $student) {
			
			if (!filter_var($student, FILTER_VALIDATE_EMAIL)) {
				return $this->returnForbiddenResponse("Provided student email address ".$student." is not valid");
			}
			
			
			
			$stud_user = $entityManager->getRepository("AppBundle\Entity\User")->findOneBy(array("email" => $student));
			
			if(!$stud_user){
				$stud_user = new User($student, $student);
				$entityManager->persist($stud_user);
			}
			
			$usr = new UserSectionRole($stud_user, $section, $takesRole);
			$entityManager->persist($usr);
			
			unset($oldUsers[$stud_user->id]);
		}
		
		# add the teachers from the teachers array
		
		$teachesRole = $entityManager->getRepository("AppBundle\Entity\Role")->findOneBy(array("role_name" => "Teaches"));
		foreach ($teachers as $sectionTeacher){
			
			if(!filter_var($sectionTeacher, FILTER_VALIDATE_EMAIL)) {
				return $this->returnForbiddenResponse("Provided teacher email address ".$sectionTeacher." is not valid");
			}
			
			
			
			$teach_user = $entityManager->getRepository("AppBundle\Entity\User")->findOneBy(array("email"=>$sectionTeacher));
			
			if(!$teach_user){
				return $this->returnForbiddenResponse("Teacher with email ".$sectionTeacher." does not exist!");
			}
			
			$this->logError("Made it first");
			if ($this->graderService->isTaking($teach_user, $section)) {
				return $this->returnForbiddenResponse($student . " is already teaching this course!");
			}
			$this->logError("Made it second");
			
			$usr = new UserSectionRole($teach_user, $section, $teachesRole);
			$entityManager->persist($usr);
			
			unset($oldUsers[$stud_user->id]);
		}
		
		
		foreach($oldUsers as $oldUser){
			
			foreach($section->assignments as $assignments){
				foreach($assignments->teams as &$team){	

					$team->users->removeElement($oldUser);

					if($team->users->count() == 0){
						$entityManager->remove($team);
					} else {
						$entityManager->persist($team);
					}
				}
			}

		}

		$entityManager->flush();

		# redirect to the section page
		$url = $this->generateUrl("section", ["sectionId" => $section->id]);

		$response = new Response(json_encode(array("redirect_url" => $url)));
		$response->headers->set("Content-Type", "application/json");
		$response->setStatusCode(Response::HTTP_OK);

		return $response;
	}

	private function getDateTime($semester, $year){

		if($semester == "Fall"){
			return [DateTime::createFromFormat("m/d/Y H:i:s", "08/01/".$year." 00:00:00"),
					DateTime::createFromFormat("m/d/Y H:i:s", "12/31/".$year." 23:59:59")];
		} else if($semester == "Spring"){
			return [DateTime::createFromFormat("m/d/Y H:i:s", "01/01/".$year." 00:00:00"),
					DateTime::createFromFormat("m/d/Y H:i:s", "05/31/".$year." 23:59:59")];
		} else {
			return [DateTime::createFromFormat("m/d/Y H:i:s", "05/01/".$year." 00:00:00"),
					DateTime::createFromFormat("m/d/Y H:i:s", "08/31/".$year." 23:59:59")];
		}

	}

	public function searchSubmissionsAction(Request $request){


		$entityManager = $this->getDoctrine()->getManager();
		// $graderService = new Grader($entityManager);

		# validate the current user
		$user = $this->get("security.token_storage")->getToken()->getUser();
		if(!$user){
			return $this->returnForbiddenResponse("You are not a user.");
		}

		$elevatedUser = $user->hasRole("ROLE_SUPER") || $user->hasRole("ROLE_ADMIN");

		# see which fields were included
		$postData = $request->request->all();

		if(!isset($postData["val"]) || trim($postData["val"]) == ""){
			return $this->returnForbiddenResponse("val must be provided for searching");			
		}

		if(!isset($postData["id"]) || trim($postData["id"]) == ""){
			return $this->returnForbiddenResponse("section id must be provided for searching");			
		}

		# VALIDATION
		$searchVals = explode(";", $postData["val"]);
		$section = $entityManager->find("AppBundle\Entity\Section", $postData["id"]);
		
		if( !($this->graderService->isTeaching($user, $section) || $this->graderService->isTaking($user, $section) || $this->graderService->isHelping($user, $section) || $elevatedUser) ){
			return $this->returnForbiddenResponse("You are not allowed to search the submissions of this section");			
		}

		$elevatedQuery = "";
		if($elevatedUser){
			$elevatedQuery = " OR 1=1";
		}

		$userTeams = $entityManager->createQueryBuilder()
						->select("t")
						->from("AppBundle\Entity\Team", "t")
						->where(":user MEMBER OF t.users")
						->setParameter("user", $user)
						->getQuery()
						->getResult();

		$data_query = $entityManager->createQueryBuilder()
				->select("s")
				->from("AppBundle\Entity\Submission", "s")
				->where("s.problem IN (?1)")
				->andWhere("s.team IN (?2)".$elevatedQuery)
				->orderBy("s.id", "DESC")
				->setParameter(1, $section->getAllProblems())
				->setParameter(2, $userTeams)
				->getQuery();

		$results = [];

		foreach($searchVals as $searchVal){					
			
			$searchVal = trim($searchVal);
			
			$paginator = new Paginator($data_query, true);

			foreach($paginator as $sub){

				if( $sub->id == $searchVal){
					$results[] = $sub;
					continue;
				}

				if( stripos($sub->problem->assignment->name, $searchVal) !== FALSE){
					$results[] = $sub;
					continue;
				}

				if( stripos($sub->problem->name, $searchVal) !== FALSE){
					$results[] = $sub;
					continue;
				}

				if( stripos($sub->user->getFullName(), $searchVal) !== FALSE){
					$results[] = $sub;
					continue;
				}

				if( stripos($sub->user->getEmail(), $searchVal) !== FALSE){
					$results[] = $sub;
					continue;
				}

				$teamStr = $sub->team->name;
				foreach($sub->team->users as $usr){
					$teamStr .= " ".$usr->getFullName()." ".$usr->getEmail();				
				}
				
				if( stripos($teamStr, $searchVal) !== FALSE){
					$results[] = $sub;
					continue;	
				}

				if( $sub->isCorrect() && stripos("Correct", $searchVal) !== FALSE){
					$results[] = $sub;
					continue;				
				} else if( stripos("Correct", $searchVal) !== FALSE ){
					continue;
				} else if( !$sub->isCorrect() && stripos($sub->getResultString(), $searchVal) !== FALSE){
					$results[] = $sub;
					continue;
				}	

				$entityManager->clear();
			}
		}

		$response = new Response(json_encode([
			"results" => $results,		
		]));
		$response->headers->set("Content-Type", "application/json");
		$response->setStatusCode(Response::HTTP_OK);

		return $response;
	}	

	private function logError($message) {
		$errorMessage = "SectionController: ".$message;
		$this->logger->error($errorMessage);
		return $errorMessage;
	}
	
	private function returnForbiddenResponse($message){		
		$response = new Response($message);
		$response->setStatusCode(Response::HTTP_FORBIDDEN);
		$this->logError($message);
		return $response;
	}
}
