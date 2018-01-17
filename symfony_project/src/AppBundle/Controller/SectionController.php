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

use AppBundle\Utils\Grader;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Psr\Log\LoggerInterface;

class SectionController extends Controller {

    public function sectionAction($sectionId) {

		$em = $this->getDoctrine()->getManager();

		$user = $this->get('security.token_storage')->getToken()->getUser();

		if(!$user){
			die("USER DOES NOT EXIST");
		}

		if(!isset($sectionId) || !($sectionId > 0)){
			die("SECTION ID WAS NOT PROVIDED OR NOT FORMATTED PROPERLY");
		}

		$section_entity = $em->find('AppBundle\Entity\Section', $sectionId);

		if(!$section_entity){
			die("SECTION DOES NOT EXIST!");
		}

		# GET ALL ASSIGNMENTS
		$qb = $em->createQueryBuilder();
		$qb->select('a')
			->from('AppBundle\Entity\Assignment', 'a')
			->where('a.section = ?1')
			->orderBy('a.start_time', 'ASC')
			->setParameter(1, $section_entity);

		$query = $qb->getQuery();
		$assignments = $query->getResult();

		# GET FUTURE ASSIGNMENTS
		$twoweeks_date = new DateTime();
		$twoweeks_date = $twoweeks_date->add(new DateInterval('P2W'));

		$qb_asgn = $em->createQueryBuilder();
		$qb_asgn->select('a')
				->from('AppBundle\Entity\Assignment', 'a')
				->where('a.section = ?1')
				->andWhere('a.end_time > ?2')
				->andWhere('a.end_time < ?3')
				->setParameter(1, $section_entity)
				->setParameter(2, new DateTime())
				->setParameter(3, $twoweeks_date)
				->orderBy('a.end_time', 'ASC');

		$asgn_query = $qb_asgn->getQuery();
		$future_assig = $asgn_query->getResult();

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

		foreach($usersectionroles as $usr){
			if($usr->role->role_name == "Takes"){
				$section_takers[] = $usr->user;
			} else if($usr->role->role_name == "Teaches"){
				$section_teachers[] = $usr->user;
			} else if($usr->role->role_name == "Helps"){
				$section_helpers[] = $usr->user;
			}
		}

		# get all of the problems to get all of the submissions
		$allprobs = [];
		foreach($section_entity->assignments as $asgn){
			foreach($asgn->problems as $prob){
				$allprobs[] = $prob;
			}
		}

		$grader = new Grader($em);

		if($user->hasRole("ROLE_SUPER") || $user->hasRole("ROLE_ADMIN") || $grader->isTeaching($user, $section_entity)){

			$qb_submissions = $em->createQueryBuilder();
			$qb_submissions->select('s')
					->from('AppBundle\Entity\Submission', 's')
					->where('s.problem IN (?1)')
					->orderBy('s.timestamp', 'DESC')
					->setParameter(1, $allprobs)
					->setMaxResults(30);

			$submission_query = $qb_submissions->getQuery();
			$submissions = $submission_query->getResult();

		} else {
			$teams = [];

			foreach($section_entity->assignments as $asgn){
				$teams[] = $grader->getTeam($user, $asgn);
			}

			$qb_submissions = $em->createQueryBuilder();
			$qb_submissions->select('s')
					->from('AppBundle\Entity\Submission', 's')
					->where('s.problem IN (?1)')
					->andWhere('s.team IN (?2)')
					->orderBy('s.timestamp', 'DESC')
					->setParameter(1, $allprobs)
					->setParameter(2, $teams)
					->setMaxResults(30);

			$submission_query = $qb_submissions->getQuery();
			$submissions = $submission_query->getResult();

		}

		$grades = [];
		foreach($section_takers as $section_taker){
			$grades[$section_taker->id] = $grader->getAllAssignmentGrades($section_taker, $section_entity);
		}

		return $this->render('section/index.html.twig', [
			'section' => $section_entity,
			'grader' => new Grader($em),
			'user' => $user,

			'assignments' => $assignments,
			'grades' => $grades,

			'future_assigs' => $future_assig,

			'recent_submissions' => $submissions,

			'section_takers' => $section_takers,
			'section_teachers' => $section_teachers,
			'section_helpers' => $section_helpers,
		]);
    }

    public function editSectionAction($sectionId) {

		$em = $this->getDoctrine()->getManager();
		$builder = $em->createQueryBuilder();

		$builder->select('c')
				->from('AppBundle\Entity\Course', 'c')
				->where('c.is_deleted = false');
		$query = $builder->getQuery();
		$courses = $query->getResult();

		$user = $this->get('security.token_storage')->getToken()->getUser();
		if(!$user){
			die("USER DOES NOT EXIST");
		}

		$users = $em->getRepository("AppBundle\Entity\User")->findAll();

		$instructors = [];

		foreach ($users as $u) {
			if($u->hasRole("ROLE_ADMIN") or $u->hasRole("ROLE_SUPER")) {
				$instructors[] = $u;
			}
		}

		if($sectionId != 0){

			if(!isset($sectionId) || !($sectionId > 0)){
				die("SECTION ID WAS NOT PROVIDED OR NOT FORMATTED PROPERLY");
			}

			$section = $em->find('AppBundle\Entity\Section', $sectionId);

			if(!$section){
				die("SECTION DOES NOT EXIST");
			}

			$section_taker_roles = [];
			$section_teacher_roles = [];


			$teaches_role = $em->getRepository('AppBundle\Entity\Role')->findOneBy(array('role_name' => 'Teaches'));
			$takes_role = $em->getRepository('AppBundle\Entity\Role')->findOneBy(array('role_name' => 'Takes'));

			foreach($section->user_roles as $ur){

				if($ur->role == $takes_role){
					$section_taker_roles[] = $ur;
				} else if($ur->role == $teaches_role){
					$section_teacher_roles[] = $ur;
				}

			}
		}

		return $this->render('section/edit.html.twig', [
			'courses' => $courses,
			'users' => $users,
			'instructors' => $instructors,
			'section' => $section,
			'section_taker_roles' => $section_taker_roles,
			'section_teacher_roles' => $section_teacher_roles,
		]);
    }

	public function cloneSectionAction($sectionId){

		$em = $this->getDoctrine()->getManager();

		$user = $this->get('security.token_storage')->getToken()->getUser();
		if(!$user){
			die("USER DOES NOT EXIST");
		}

		$section = $em->find('AppBundle\Entity\Section', $sectionId);

		if(!$section){
			die("SECTION DOES NOT EXIST");
		}

		$newSection = clone $section;
		$em->persist($newSection);

		$em->flush();

		return $this->redirectToRoute('section_edit', ['sectionId' => $newSection->id]);
	}

	public function deleteSectionAction($sectionId){

		$em = $this->getDoctrine()->getManager();

		# get the section
		if(!isset($sectionId) || !($sectionId > 0)){
			die("SECTION ID WAS NOT PROVIDED OR NOT FORMATTED PROPERLY");
		}
		$section = $em->find('AppBundle\Entity\Section', $sectionId);

		if(!$section){
			die("SECTION DOES NOT EXIST");
		}

		$user = $this->get('security.token_storage')->getToken()->getUser();
		if(!$user){
			die("USER DOES NOT EXIST");
		}

		# validate the user
		if(!$user->hasRole("ROLE_SUPER") && !$user->hasRole("ROLE_ADMIN")){
			die("YOU ARE NOT ALLOWED TO DELETE THIS SECTION");

		}

		$section->is_deleted = !$section->is_deleted;
		$em->flush();

		return $this->redirectToRoute('homepage');
	}

	public function modifyPostAction(Request $request){

		$em = $this->getDoctrine()->getManager();

		# validate the current user
		$user = $this->get('security.token_storage')->getToken()->getUser();
		if(!$user){
			return $this->returnForbiddenResponse("You are not a user.");
		}

		# only super users and admins can make/edit a section
		if(!$user->hasRole("ROLE_SUPER") && !$user->hasRole("ROLE_ADMIN")){
			return $this->returnForbiddenResponse("You do not have permission to make a section.");
		}

		# see which fields were included
		$postData = $request->request->all();

		# check mandatory fields
		if(!isset($postData['name']) || trim($postData['name']) == "" || !isset($postData['course']) || !isset($postData['semester']) || !isset($postData['year'])){
			return $this->returnForbiddenResponse("Not every required field is provided.");
		} else {

			# validate the year
			if(!is_numeric(trim($postData['year']))){
				return $this->returnForbiddenResponse($postData['year']." is not a valid year");
			}

			# validate the semester
			if(trim($postData['semester']) != 'Fall' && trim($postData['semester']) != 'Spring' && trim($postData['semester']) != 'Summer'){
				return $this->returnForbiddenResponse($postData['semester']." is not a valid semester");
			}
		}

		# create new section
		if($postData['section'] == 0){
			$section = new Section();
		} else {

			if(!isset($postData['section']) || !($postData['section'] > 0)){
				die("SECTION ID WAS NOT PROVIDED OR NOT FORMATTED PROPERLY");
			}
			$section = $em->find('AppBundle\Entity\Section', $postData['section']);

			if(!$section){
				return $this->returnForbiddenResponse("Section ".$postData['section']." does not exist");
			}
		}

		# get the course
		if(!isset($postData['course']) || !($postData['course'] > 0)){
			die("COURSE ID WAS NOT PROVIDED OR NOT FORMATTED PROPERLY");
		}

		$course = $em->find('AppBundle\Entity\Course', $postData['course']);
		if(!$course){
			return $this->returnForbiddenResponse("Course provided does not exist.");
		}

		# set the necessary fields
		$section->name = trim($postData['name']);
		$section->course = $course;
		$section->semester = $postData['semester'];
		$section->year = (int)trim($postData['year']);

		# see if the dates were provided or if we will do them automatically
		$dates = $this->getDateTime($postData['semester'], $postData['year']);
		if(isset($postData['start_time']) && $postData['start_time'] != ''){
			$customStartTime = DateTime::createFromFormat("m/d/Y H:i:s", $postData['start_time']." 00:00:00");

			if(!$customStartTime || $customStartTime->format("m/d/Y") != $postData['start_time']){
				return $this->returnForbiddenResponse("Provided invalid start time ". $postData['start_time']);
			} else {
				$section->start_time = $customStartTime;
			}

		} else {
			$section->start_time = $dates[0];
		}

		if(isset($postData['end_time']) && $postData['end_time'] != ''){
			$customEndTime = DateTime::createFromFormat("m/d/Y H:i:s", $postData['end_time']." 23:59:59");

			if(!$customEndTime || $customEndTime->format("m/d/Y") != $postData['end_time']){
				return $this->returnForbiddenResponse("Provided invalid end time ". $postData['end_time']);
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

		$em->persist($section);

    # validate the students csv
		$students = array_unique(json_decode($postData['students']));

    foreach ($students as $student) {

			if (!filter_var($student, FILTER_VALIDATE_EMAIL)) {
				return $this->returnForbiddenResponse("Provided student email address ".$student." is not valid");
			}
		}

    # vallidate teacher csv
    $teachers = array_unique(json_decode($postData['teachers']));

    foreach ($teachers as $teacher){

			if(!filter_var($teacher, FILTER_VALIDATE_EMAIL)) {
				return $this->returnForbiddenResponse("Provided teacher email address ".$teacher." is not valid");
			}
		}


		if($postData['section'] == 0 && count(json_decode($postData['teachers'])) == 0){

			# add the current user as a role
			$role = $em->getRepository('AppBundle\Entity\Role')->findOneBy(array('role_name' => 'Teaches'));
			$usr = new UserSectionRole($user, $section, $role);
			$em->persist($usr);

		} else if($postData['section'] != 0){

			foreach($section->user_roles as $ur){
				$em->remove($ur);
			}

			$em->flush();
		}

    # add students from the students array

		$takes_role = $em->getRepository('AppBundle\Entity\Role')->findOneBy(array('role_name' => 'Takes'));
		foreach ($students as $student) {

			if (!filter_var($student, FILTER_VALIDATE_EMAIL)) {
				return $this->returnForbiddenResponse("Provided student email address ".$student." is not valid");
			}

			$stud_user = $em->getRepository('AppBundle\Entity\User')->findOneBy(array('email' => $student));

			if(!$stud_user){
				$stud_user = new User($student, $student);
				$em->persist($stud_user);
			}

			$usr = new UserSectionRole($stud_user, $section, $takes_role);
			$em->persist($usr);
		}

		# add the teachers from the teachers array

		$teaches_role = $em->getRepository('AppBundle\Entity\Role')->findOneBy(array('role_name' => 'Teaches'));
		foreach ($teachers as $teacher){

			if(!filter_var($teacher, FILTER_VALIDATE_EMAIL)) {
				return $this->returnForbiddenResponse("Provided teacher email address ".$teacher." is not valid");
			}

			$teach_user = $em->getRepository('AppBundle\Entity\User')->findOneBy(array('email'=>$teacher));

			if(!$teach_user){
				return $this->returnForbiddenResponse("Teacher with email ".$teacher." does not exist!");
			}

			$usr = new UserSectionRole($teach_user, $section, $teaches_role);
			$em->persist($usr);
		}


		$em->flush();

		# redirect to the section page
		$url = $this->generateUrl('section', ['sectionId' => $section->id]);

		$response = new Response(json_encode(array('redirect_url' => $url)));
		$response->headers->set('Content-Type', 'application/json');
		$response->setStatusCode(Response::HTTP_OK);

		return $response;
	}

	private function getDateTime($semester, $year){

		if($semester == 'Fall'){
			return [DateTime::createFromFormat("m/d/Y H:i:s", "08/01/".$year." 00:00:00"),
					DateTime::createFromFormat("m/d/Y H:i:s", "12/31/".$year." 23:59:59")];
		} else if($semester == 'Spring'){
			return [DateTime::createFromFormat("m/d/Y H:i:s", "01/01/".$year." 00:00:00"),
					DateTime::createFromFormat("m/d/Y H:i:s", "05/31/".$year." 23:59:59")];
		} else {
			return [DateTime::createFromFormat("m/d/Y H:i:s", "05/01/".$year." 00:00:00"),
					DateTime::createFromFormat("m/d/Y H:i:s", "08/31/".$year." 23:59:59")];
		}

	}

	private function returnForbiddenResponse($message){
		$response = new Response($message);
		$response->setStatusCode(Response::HTTP_FORBIDDEN);
		return $response;
	}
}
