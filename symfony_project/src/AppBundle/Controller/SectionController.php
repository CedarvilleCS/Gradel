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

use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Doctrine\ORM\Tools\Pagination\Paginator;

use Symfony\Component\Config\Definition\Exception\Exception;

use Psr\Log\LoggerInterface;

class SectionController extends Controller {

    public function sectionAction($sectionId) {

		$em = $this->getDoctrine()->getManager();
		$grader = new Grader($em);

		$user = $this->get('security.token_storage')->getToken()->getUser();
		if(!$user){
			die("USER DOES NOT EXIST");
		}

		# VALIDATION
		$section = $em->find('AppBundle\Entity\Section', $sectionId);

		if(!$section){
			die("SECTION DOES NOT EXIST!");
		}
		
		# REDIRECT TO CONTEST PATH IF NEED BE
		if($section->course->is_contest){
			return $this->redirectToRoute('contest', ['contestId' => $section->id]);
		}
		
		if( !($user->hasRole("ROLE_ADMIN") || $user->hasRole("ROLE_SUPER") || (isset($section) && ($grader->isTeaching($user, $section) || $grader->isTaking($user, $section)))) ){
			die("YOU ARE NOT ALLOWED TO BE HERE");
		}

		# GET ALL ASSIGNMENTS
		$qb = $em->createQueryBuilder();
		$qb->select('a')
			->from('AppBundle\Entity\Assignment', 'a')
			->where('a.section = ?1')
			->orderBy('a.start_time', 'ASC')
			->setParameter(1, $section);

		$query = $qb->getQuery();
		$assignments = $query->getResult();

		# GET ALL USERS
		$qb_user = $em->createQueryBuilder();
		$qb_user->select('usr')
			->from('AppBundle\Entity\UserSectionRole', 'usr')
			->where('usr.section = ?1')
			->setParameter(1, $section);

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
		
		# GET FUTURE ASSIGNMENTS
		$twoweeks_date = new DateTime();
		$twoweeks_date = $twoweeks_date->add(new DateInterval('P2W'));

		$qb_asgn = $em->createQueryBuilder();
		$qb_asgn->select('a')
				->from('AppBundle\Entity\Assignment', 'a')
				->where('a.section = ?1')
				->andWhere('a.end_time > ?2')
				->andWhere('a.end_time < ?3')
				->setParameter(1, $section)
				->setParameter(2, new DateTime())
				->setParameter(3, $twoweeks_date)
				->orderBy('a.end_time', 'ASC');

		$asgn_query = $qb_asgn->getQuery();
		$future_assigs = $asgn_query->getResult();
		
		# GATHER SUBMISSIONS
		# get all of the problems to get all of the submissions
		$allprobs = [];
		foreach($section->assignments as $asgn){
			foreach($asgn->problems as $prob){
				$allprobs[] = $prob;
			}
		}

		if($user->hasRole("ROLE_SUPER") || $user->hasRole("ROLE_ADMIN") || $grader->isTeaching($user, $section) || $grader->isJudging($user, $section)){
			
			$submissions = [];

		} else {
			
			$submissions = [];
		}
		
		// get assignment grades for the student
		$grades = [];
		$subs = [];
		foreach($section_takers as $section_taker){
			
			$correct_sub_ids = [];
			
			$grades[$section_taker->id] = $grader->getAllAssignmentGrades($section_taker, $section);
			
			foreach ($assignments as $assig){
				$probs = $assig->problems;
				$team = $grader->getTeam($section_taker, $assig);
				
				foreach($probs as $prob){
					
					$qb_submissions = $em->createQueryBuilder();
					$qb_submissions->select('s')
							->from('AppBundle\Entity\Submission', 's')
							->where('s.problem = (?1)')
							->andWhere('s.team = (?2)')
							->andWhere('s.best_submission = 1')
							->setParameter(1, $prob)
							->setParameter(2, $team);
					$submission_query = $qb_submissions->getQuery();
					$submission = $submission_query->getOneOrNullResult();
					$correct_sub_ids[$assig->id][$prob->id]=$submission->id;
					
				}
			}
			$subs[$section_taker->id] = $correct_sub_ids;
			
		}


		// get the users most recent submissions (top 15)
		$submissions = $em->createQueryBuilder()
					->select('s')
					->from('AppBundle\Entity\Submission', 's')
					->where('s.user = (?1)')
					->andWhere('s.problem IN (?2)')
					->orderBy('s.id', 'DESC')
					->setParameter(1, $user)
					->setParameter(2, $section->getAllProblems())
					->setMaxResults(15)
					->getQuery()
					->getResult();

		// array of arrays that contain a main text and a subtext that will be used for autocompleting searches
		// ['Timothy Smith', 'timothyglensmith@cedarville.edu']
		// ['Get the Sum', 'Homework #2']
		// ['Wrong Answer', 'Incorrect']
		$suggestions = [];

		// get the users
		foreach($section_takers as $taker){
			$suggestions[] = [$taker->getFullName(), $taker->getEmail()];
		}

		// get the teachers
		foreach($section_teachers as $teacher){
			$suggestions[] = [$teacher->getFullName(), $teacher->getEmail()];
		}

		// get the helpers
		foreach($section_helpers as $helper){
			$suggestions[] = [$helper->getFullName(), $helper->getEmail()];
		}

		//  get the problems
		foreach($allprobs as $prob){
			$suggestions[] = [$prob->name, $prob->assignment->name];			
		}

		// get the assignments and teams
		foreach($section->assignments as $assign){
			$suggestions[] = [$assign->name, ''];			

			foreach($assign->teams as $tm){

				if($tm->users->count() > 1){
					$suggestions[] = [$tm->name, ''];
				}
			}
		}

		// get the correct types
		$suggestions[] = ['Correct', ''];
		$suggestions[] = ['Incorrect', ''];
		$suggestions[] = ['Wrong Answer', 'Incorrect'];
		$suggestions[] = ['Runtime Error', 'Incorrect'];
		$suggestions[] = ['Time Limit Error', 'Incorrect'];
		$suggestions[] = ['Compile Error', 'Incorrect'];
				
		return $this->render('section/index.html.twig', [
			'section' => $section,
			
			'grader' => new Grader($em),
			'user' => $user,
			'team' => $team,

			'grades' => $grades,

			'user_assig_prob_sub' => $subs,

			'submissions' => $submissions,
			
			'future_assigs' => $future_assigs,

			'user_impersonators' => $section_takers,

			'section_takers' => $section_takers,
			'section_teachers' => $section_teachers,
			'section_helpers' => $section_helpers,

			'search_suggestions' => $suggestions,
		]);
	}

    public function editSectionAction($sectionId) {

		$em = $this->getDoctrine()->getManager();
		$grader = new Grader($em);

		$user = $this->get('security.token_storage')->getToken()->getUser();
		if(!$user){
			die("USER DOES NOT EXIST");
		}


		if($sectionId != 0){

			if(!isset($sectionId) || !($sectionId > 0)){
				die("SECTION ID WAS NOT PROVIDED OR NOT FORMATTED PROPERLY");
			}

			$section = $em->find('AppBundle\Entity\Section', $sectionId);

			if(!$section){
				die("SECTION DOES NOT EXIST");
			}

			# REDIRECT TO CONTEST IF NEED BE
			if($section->course->is_contest){
				return $this->redirectToRoute('contest_edit', ['contestId' => $section->id]);	
			}

			if($section->master){
				return $this->redirectToRoute('section_edit', ['sectionId' => $section->master->id]);
			}
		}
		
		if( !($user->hasRole("ROLE_ADMIN") || $user->hasRole("ROLE_SUPER") || (isset($section) && $grader->isTeaching($user, $section))) ){
			die("YOU ARE NOT ALLOWED TO BE HERE");
		}

		$builder = $em->createQueryBuilder();
		$builder->select('c')
				->from('AppBundle\Entity\Course', 'c')
				->where('c.is_deleted = false');
		$query = $builder->getQuery();
		$courses = $query->getResult();
		

		return $this->render('section/edit.html.twig', [
			'courses' => $courses,
			'section' => $section,
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

		foreach($section->slaves as $slave){
			$newSlave = clone $slave;
			
			$newSection->slaves->add($newSlave);
			$newSlave->master = $newSection;
			$em->persist($newSlave);
		}

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

		if(!$section || $section->master != null){
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

		foreach($section->slaves as &$slave){
			$slave->is_deleted = !$slave->is_deleted;
		}
		$em->flush();

		return $this->redirectToRoute('homepage');
	}

	public function modifyPostAction(Request $request){

		$em = $this->getDoctrine()->getManager();
		$grader = new Grader($em);

		# validate the current user
		$user = $this->get('security.token_storage')->getToken()->getUser();
		if(!$user){
			return $this->returnForbiddenResponse("You are not a user.");
		}


		# see which fields were included
		$postData = $request->request->all();

		# get the course
		if(!isset($postData['course'])){
			die("COURSE ID WAS NOT PROVIDED OR NOT FORMATTED PROPERLY");
		}

		$course = $em->find('AppBundle\Entity\Course', $postData['course']);
		if(!$course){
			return $this->returnForbiddenResponse("Course provided does not exist.");
		}
					
		if($course->is_contest){
			return $this->returnForbiddenResponse("This is not the correct post to make a contest");
		}

		# check mandatory fields
		# validate the year
		if(!isset($postData['year']) || !is_numeric($postData['year'])){
			return $this->returnForbiddenResponse($postData['year']." is not a valid year");
		}
		$year = (int) $postData['year'];

		# validate the semester
		$validsems = ["Fall", "Spring", "Winter", "Summer"];

		if(!isset($postData['semester']) || !in_array($postData['semester'], $validsems)){
			return $this->returnForbiddenResponse($postData['semester']." is not a valid semester");
		}
		$semester = $postData['semester'];

		# see if the dates were provided or if we will do them automatically
		$dates = $this->getDateTime($postData['semester'], $postData['year']);
		if(isset($postData['start_time']) && $postData['start_time'] != ''){
			$customStartTime = DateTime::createFromFormat("m/d/Y H:i:s", $postData['start_time']." 00:00:00");

			if(!$customStartTime || $customStartTime->format("m/d/Y") != $postData['start_time']){
				return $this->returnForbiddenResponse("Provided invalid start time ". $postData['start_time']);
			} else {

				$start_time = $customStartTime;
			}

		} else {
			$start_time = $dates[0];
		}

		if(isset($postData['end_time']) && $postData['end_time'] != ''){
			$customEndTime = DateTime::createFromFormat("m/d/Y H:i:s", $postData['end_time']." 23:59:59");

			if(!$customEndTime || $customEndTime->format("m/d/Y") != $postData['end_time']){
				return $this->returnForbiddenResponse("Provided invalid end time ". $postData['end_time']);
			} else {
				$end_time = $customEndTime;
			}

		} else {
			$end_time = $dates[1];
		}

		# validate that the end time is after the start time
		if($end_time <= $start_time){
			return $this->returnForbiddenResponse("The end time must be after the start time for the section");
		}

		$postSections = json_decode($postData['sections']);

		if(count($postSections) < 1){
			return $this->returnForbiddenResponse("No sections were provided");
		}

		$deletedSections = json_decode($postData['deleted_sections']);

		$master_id = $postData['master_section'];

		$em->getConnection()->beginTransaction();

		try{

			$sections = [];
			foreach($postSections as $jsonSection) {

				if(in_array($jsonSection->id, $deletedSections)){
					throw new Exception("You can't edit and delete a section");
				}
			
				# create new section
				if($jsonSection->id == 0){
					
					# only super users and admins can make/edit a section
					if(!$user->hasRole("ROLE_SUPER") && !$user->hasRole("ROLE_ADMIN")){
						throw new Exception("You do not have permission to make a section");
					}
					
					$section = new Section();
				} 
				# get old section
				else if(isset($jsonSection->id)) {

					$section = $em->find('AppBundle\Entity\Section', $jsonSection->id);

					if(!$section){
						throw new Exception("Section does not exist");
					}
					
					# only super users and admins can make/edit a section
					if(! ($user->hasRole("ROLE_SUPER") || $user->hasRole("ROLE_ADMIN") || $grader->isTeaching($user, $section)) ){
						throw new Exception("You do not have permission to edit this section");
					}		
				}
				# error
				else {
					throw new Exception("Section was not given an id");
				}

				if(!isset($jsonSection->name) || trim($jsonSection->name) == ""){
					throw new Exception("Section was not given a name");
				}

				$section->name = trim($jsonSection->name);
				$section->course = $course;
				$section->semester = $semester;
				$section->year = $year;

				$section->start_time = $start_time;
				$section->end_time = $end_time;

				$section->master = null;
				$section->slaves = new ArrayCollection();

				if($master_id && $section->id === $master_id){
					$master_section = $section;
				}
			
				# validate the students and teaches csvs for proper email addresses
				$students = array_unique($jsonSection->students);
				$teachers = array_unique($jsonSection->teachers);

				if(count($students) < 1){
					throw new Exception(json_encode($jsonSection));
				}

				$both = array_merge($students, $teachers);

				foreach ($both as $person) {
					if (!filter_var($person, FILTER_VALIDATE_EMAIL)) {
						throw new Exception("Provided email address ".$person." is not valid");
					}

					if (in_array($person, $teachers) && in_array($person, $students)) {
						throw new Exception($person." appears in both the teacher and student list");
					}
				}

				$em->persist($section);
				$em->flush();

				# get list of old users
				$oldUsers = [];
				foreach($section->user_roles as $ur){
					$em->remove($ur);
					$oldUsers[$ur->user->id] = $ur->user;
				}
				$em->flush(); 

				# add yourself as the teacher if none were provided
				if(count($teachers) < 1){
					# add the current user as a role
					$role = $em->getRepository('AppBundle\Entity\Role')->findOneBy(array('role_name' => 'Teaches'));
					$usr = new UserSectionRole($user, $section, $role);
					$em->persist($usr);
				}

				# add students from the students array
				$takes_role = $em->getRepository('AppBundle\Entity\Role')->findOneBy(array('role_name' => 'Takes'));
				foreach ($students as $student) {
					
					$stud_user = $em->getRepository('AppBundle\Entity\User')->findOneBy(array('email' => $student));

					if(!$stud_user){
						$stud_user = new User($student, $student);
						$em->persist($stud_user);
					}

					$usr = new UserSectionRole($stud_user, $section, $takes_role);
					$em->persist($usr);

					unset($oldUsers[$stud_user->id]);
				}

				# add the teachers from the teachers array
				$teaches_role = $em->getRepository('AppBundle\Entity\Role')->findOneBy(array('role_name' => 'Teaches'));
				foreach ($teachers as $teacher){

					$teach_user = $em->getRepository('AppBundle\Entity\User')->findOneBy(array('email'=>$teacher));

					if(!$teach_user){
						throw new Exception("Teacher with email ".$teacher." does not exist!");
					}

					if ($grader->isTaking($teach_user, $section)) {
						throw new Exception($student . " is already teaching this course!");
					}

					$usr = new UserSectionRole($teach_user, $section, $teaches_role);
					$em->persist($usr);
				}

				# remove all of the old users (that are no longer in their section) from their teams in that section
				foreach($oldUsers as $oldUser){

					foreach($section->assignments as $asgn){
						foreach($asgn->teams as &$team){	

							$team->users->removeElement($oldUser);

							if($team->users->count() == 0){
								$em->remove($team);
							} else {
								$em->persist($team);
							}
						}
					}
				}

				$sections[] = $section;
			}

			# delete sections
			foreach($deletedSections as $delSection){				

				$section = $em->find('AppBundle\Entity\Section', $delSection);

				if(!$section){
					throw new Exception("Section does not exist");
				}
				
				# only super users and admins can delete a section
				if(! ($user->hasRole("ROLE_SUPER") || $user->hasRole("ROLE_ADMIN") || $grader->isTeaching($user, $section)) ){
					throw new Exception("You do not have permission to delete this section");
				}

				$em->remove($section);
				$em->flush();
			}


			if(!$master_section){
				$master_section = $sections[0];
			}

			$master_section->master = null;
			foreach($sections as &$section){

				if($section != $master_section){
					$section->master = $master_section;
					$master_section->slaves->add($section);
				}
			}

			$em->flush();
			$em->getConnection()->commit();
		}
		catch(Exception $e){
			$em->getConnection()->rollBack();

			return $this->returnForbiddenResponse($e->getMessage());	
		}

		# redirect to the section page
		if(count($sections) > 1){
			$url = $this->generateUrl('homepage');			
		} else {
			$url = $this->generateUrl('section', ['sectionId' => $section->id]);
		}

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

	public function searchSubmissionsAction(Request $request){


		$em = $this->getDoctrine()->getManager();
		$grader = new Grader($em);

		# validate the current user
		$user = $this->get('security.token_storage')->getToken()->getUser();
		if(!$user){
			return $this->returnForbiddenResponse("You are not a user.");
		}

		$elevatedUser = $user->hasRole("ROLE_SUPER") || $user->hasRole("ROLE_ADMIN");

		# see which fields were included
		$postData = $request->request->all();

		if(!isset($postData['val']) || trim($postData['val']) == ''){
			return $this->returnForbiddenResponse("val must be provided for searching");			
		}

		if(!isset($postData['id']) || trim($postData['id']) == ''){
			return $this->returnForbiddenResponse("section id must be provided for searching");			
		}

		# VALIDATION
		$searchVals = explode(';', $postData['val']);
		$section = $em->find('AppBundle\Entity\Section', $postData['id']);
		
		if( !($grader->isTeaching($user, $section) || $grader->isTaking($user, $section) || $grader->isHelping($user, $section) || $elevatedUser) ){
			return $this->returnForbiddenResponse("You are not allowed to search the submissions of this section");			
		}

		$elevatedQuery = '';
		if($elevatedUser){
			$elevatedQuery = ' OR 1=1';
		}

		$userTeams = $em->createQueryBuilder()
						->select('t')
						->from('AppBundle\Entity\Team', 't')
						->where(':user MEMBER OF t.users')
						->setParameter('user', $user)
						->getQuery()
						->getResult();

		$data_query = $em->createQueryBuilder()
				->select('s')
				->from('AppBundle\Entity\Submission', 's')
				->where('s.problem IN (?1)')
				->andWhere('s.team IN (?2)'.$elevatedQuery)
				->orderBy('s.id', 'DESC')
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
					$teamStr .= ' '.$usr->getFullName().' '.$usr->getEmail();				
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

				$em->clear();
			}
		}

		$response = new Response(json_encode([
			'results' => $results,		
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
