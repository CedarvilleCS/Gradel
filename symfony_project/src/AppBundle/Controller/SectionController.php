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

		$section_entity = $em->find('AppBundle\Entity\Section', $sectionId);

		if(!$section_entity){
			die("SECTION DOES NOT EXIST!");
		}

		# GET ALL ASSIGNMENTS
		$qb = $em->createQueryBuilder();
		$qb->select('a')
			->from('AppBundle\Entity\Assignment', 'a')
			->where('a.section = ?1')
			->orderBy('a.end_time', 'ASC')
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
		$qb_submissions = $em->createQueryBuilder();
		$qb_submissions->select('s')
				->from('AppBundle\Entity\Submission', 's')
				->where('s.problem IN (?1)')
				->orderBy('s.timestamp', 'DESC')
				->setParameter(1, $allprobs)
				->setMaxResults(30);

		$submission_query = $qb_submissions->getQuery();
		$submissions = $submission_query->getResult();

		$grader = new Grader($em);
		
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

	public function newSectionAction() {

		$em = $this->getDoctrine()->getManager();
		$builder = $em->createQueryBuilder();

		$builder->select('c')
				->from('AppBundle\Entity\Course', 'c')
				->where('1 = 1');
		$query = $builder->getQuery();
		$sections = $query->getResult();


		$section = new Section();
		$form = $this->createFormBuilder($section)
					->add('name', TextType::class)
					->add('year', DateType::class)
					->add('save', SubmitType::class, array('label' => 'Create Section'))
					->getForm();

		$user = $this->get('security.token_storage')->getToken()->getUser();
					
		$builder = $em->createQueryBuilder();
		$builder->select('u')
				->from('AppBundle\Entity\User', 'u')
				->where('1 = 1');
		$query = $builder->getQuery();
		$users = $query->getResult();

		return $this->render('section/new.html.twig', [
			'sections' => $sections,
			'users' => $users,
			'form' => $form->createView(),
		]);
	}
	
	public function deleteAction($sectionId){
		
		$em = $this->getDoctrine()->getManager();

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
		
		$section->is_deleted = true;
		$em->flush();
		return $this->redirectToRoute('homepage');
	}

	# called when the edit button is clicked on an already existing section
    public function editSectionAction($sectionId) {
      $em = $this->getDoctrine()->getManager();
	  
	  $section = $em->find('AppBundle\Entity\Section', $sectionId);	  
	  if(!$section){
		die("SECTION DOES NOT EXIST");
	  }     

	  # get all of the courses for the dropdown
      $builder = $em->createQueryBuilder();
      $builder->select('c')
              ->from('AppBundle\Entity\Course', 'c')
              ->where('1 = 1');
      $query = $builder->getQuery();
      $sections = $query->getResult();

	  # get all of the users for the dropdown
      $builder = $em->createQueryBuilder();
      $builder->select('u')
              ->from('AppBundle\Entity\User', 'u')
              ->where('1 = 1');
      $query = $builder->getQuery();
      $users = $query->getResult();
	  
	  # get all of the users taking the section
	  $takes_role = $em->getRepository('AppBundle\Entity\Role')->findOneBy(array('role_name' => 'Takes'));
      $builder = $em->createQueryBuilder();
      $builder->select('u')
              ->from('AppBundle\Entity\UserSectionRole', 'u')
              ->where('u.section = ?1')
              ->andWhere('u.role = ?2')
              ->setParameter(1, $section)
              ->setParameter(2, $takes_role);
      $query = $builder->getQuery();
      $section_takers = $query->getResult();	

		$qb = $em->createQueryBuilder();
		$qb->select('a')
			->from('AppBundle\Entity\Assignment', 'a')
			->where('a.section = ?1')
			->orderBy('a.end_time', 'ASC')
			->setParameter(1, $section);

		$query = $qb->getQuery();
		$assignments = $query->getResult();

      return $this->render('section/edit.html.twig', [
        'section' => $section,
        'sectionId' => $sectionId,
        'sections' => $sections,
        'usr' => $section_takers,
        'users' => $users,
		'assignments' => $assignments,
		'edit' => true,
      ]);
    }

    public function editQueryAction(Request $request, $sectionId, $courseId, $name, $students, $semester, $year, $start_time, $end_time, $is_public, $is_deleted) {
		
		$em = $this->getDoctrine()->getManager();

		$section = $em->find('AppBundle\Entity\Section', $sectionId);
		
		$user = $this->get('security.token_storage')->getToken()->getUser();
		if(!$user){
			die("USER DOES NOT EXIST");
		}
		
		# make sure the user can add a new section
		if(!$user->hasRole("ROLE_SUPER") && !$user->hasRole("ROLE_ADMIN")){
			die("YOU ARE NOT ALLOWED TO MAKE A NEW SECTION"); 
		}

		if(!$section && $sectionId != 0){
			die("SECTION DOES NOT EXIST!");
		} 
		# create a new section if the sectionId = 0
		else if($sectionId == 0){				
			$section = new Section();
		}
		
		# get the course
		$course = $em->find('AppBundle\Entity\Course', $courseId);
		if(!$course){
			die("COURSE DOES NOT EXIST!");
		}
		
		$section->name = $name;
		$section->course = $course;
		$section->semester = $semester;
		$section->year = $year;
		$section->start_time =  new DateTime("now");
		$section->end_time = new DateTime("now");
		$section->is_deleted = $is_deleted;
		$section->is_public = $is_public;

		$em->persist($section);

		if($sectionId == 0){
			$role = $em->getRepository('AppBundle\Entity\Role')->findOneBy(array('role_name' => 'Teaches'));
			$usr = new UserSectionRole($user, $section, $role);
			
			$em->persist($usr);
		}
			
		$em->flush();

	    # remove all of the old roles
		$role = $em->getRepository('AppBundle\Entity\Role')->findOneBy(array('role_name' => 'Takes'));

		$builder = $em->createQueryBuilder();
		$builder->select('u')
			->from('AppBundle\Entity\UserSectionRole', 'u')
			->where('u.section = ?1')
			->andWhere('u.role = ?2')
			->setParameter(1, $section)
			->setParameter(2, $role);
		$query = $builder->getQuery();
		$usr = $query->getResult();

		foreach ($usr as $u) {
			$em->remove($u);
		}
		$em->flush();

		# add the roles back in
		foreach (json_decode($students) as $student) {

			if ($student != "") {
				$user = $em->getRepository('AppBundle\Entity\User')->findOneBy(array('email' => $student));

				$usr = new UserSectionRole($user, $section, $role);
				$em->persist($usr);
				$em->flush();
			}
		}

		return $this->redirectToRoute('section', ['sectionId' => $section->id]);
    }

    public function insertSectionAction(Request $request, $courseId, $name, $students, $semester, $year, $start_time, $end_time, $is_public, $is_deleted) {
		$sectionId = 0;	
		return $this->editQueryAction($request, $sectionId, $courseId, $name, $students, $semester, $year, $start_time, $end_time, $is_public, $is_deleted);
    }

	// the CURRENT section creation controller
	// the other one should be removed
	public function newSectionPostAction(Request $request){
				
		$em = $this->getDoctrine()->getManager();
		
		# validate the current user
		$user = $this->get('security.token_storage')->getToken()->getUser();
		if(!$user){			
			return $this->returnForbiddenResponse("You are not a user.");
		}
		
		# only super users and admins can make a section
		if(!$user->hasRole("ROLE_SUPER") && !$user->hasRole("ROLE_ADMIN")){			
			return $this->returnForbiddenResponse("You do not have permission to make a section.");
		}
		
		# see which fields were included
		$postData = $request->request->all();
		
		# check mandatory fields
		if(!$postData['name'] || !$postData['course'] || !$postData['semester'] || !$postData['year']){
			return $this->returnForbiddenResponse("Not every required field is provided.");
		} else {
			
			# validate the year
			if(!is_numeric($postData['year'])){
				return $this->returnForbiddenResponse($postData['year']." is not a valid year");
			}

			# validate the semester
			if($postData['semester'] != 'Fall' && $postData['semester'] != 'Spring' && $postData['semester'] != 'Summer'){
				return $this->returnForbiddenResponse($postData['semester']." is not a valid semester");
			}
		}
		
		# create new section
		$section = new Section();
		
		# get the course
		$course = $em->find('AppBundle\Entity\Course', $postData['course']);
		if(!$course){
			return $this->returnForbiddenResponse("Course provided does not exist.");
		}
		
		# set the necessary fields
		$section->name = $postData['name'];
		$section->course = $course;
		$section->semester = $postData['semester'];
		$section->year = $postData['year'];
		
		# see if the dates were provided or if we will do them automatically
		$dates = $this->getDateTime($postData['semester'], $postData['year']);
		if($postData['start_time'] && $postData['start_time'] != ''){
			$customStartTime = DateTime::createFromFormat("m/d/Y H:i:s", $postData['start_time']." 00:00:00");
			
			if(!$customStartTime || $customStartTime->format("m/d/Y") != $postData['start_time']){
				return $this->returnForbiddenResponse("Provided invalid start time ". $postData['start_time']);
			} else {
				$section->start_time = $customStartTime;
			}
			
		} else {
			$section->start_time = $dates[0];
		}
		
		if($postData['end_time'] && $postData['end_time'] != ''){
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

		# set the teacher to the person who made the section
		$role = $em->getRepository('AppBundle\Entity\Role')->findOneBy(array('role_name' => 'Teaches'));
		
		$usr = new UserSectionRole($user, $section, $role);
		$em->persist($usr);			
				
		# add the students if there were any
		$role = $em->getRepository('AppBundle\Entity\Role')->findOneBy(array('role_name' => 'Takes'));
		foreach (json_decode($postData['students']) as $student) {

			if ($student != "") {
				$user = $em->getRepository('AppBundle\Entity\User')->findOneBy(array('email' => $student));

				$usr = new UserSectionRole($user, $section, $role);
				$em->persist($usr);
			}
		}
		
		$em->flush();		
		
		# redirect to the section page
		$url = $this->generateUrl('section', ['sectionId' => $section->id]);		
		
		$response = new Response(json_encode(array('redirect_url' => $url, 'start_time' => $section->start_time)));
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
