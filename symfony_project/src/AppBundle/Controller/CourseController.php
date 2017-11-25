<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Entity\Course;
use AppBundle\Entity\Role;
use AppBundle\Entity\Section;

use AppBundle\Utils\Grader;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Psr\Log\LoggerInterface;

class CourseController extends Controller {

    public function coursesAction() {

		$em = $this->getDoctrine()->getManager();

		$user = $this->get('security.token_storage')->getToken()->getUser();

		if(!$user){
			die("USER DOES NOT EXIST");
		}

		if(!$user->hasRole("ROLE_SUPER") && !$user->hasRole("ROLE_USER")){
			
			die("YOU DO NOT HAVE PERMISSION TO SEE THIS PAGE!");
		}
		
		$courses = $em->getRepository("AppBundle\Entity\Course")->findAll();
		
		usort($courses, array('AppBundle\Entity\Course', 'cmp'));
		
		return $this->render('course/index.html.twig', [
			'courses' => $courses,
		]);
    }

    public function editCourseAction($courseId) {
		
		$em = $this->getDoctrine()->getManager();
		
		$user = $this->get('security.token_storage')->getToken()->getUser();
		if(!$user){
			die("USER DOES NOT EXIST");
		}
		
		if(!$user->hasRole("ROLE_SUPER") && !$user->hasRole("ROLE_ADMIN")){
			die("YOU DO NOT HAVE PERMISSION TO SEE THIS PAGE!"); 
		}

		if($courseId != 0){
			$course = $em->find('AppBundle\Entity\Course', $courseId);
		}
		
		$courses = $em->getRepository("AppBundle\Entity\Course")->findAll();
		
		return $this->render('course/edit.html.twig', [
			'course' => $course,
			'courses' => $courses,
		]);
    }
	
	/*
	public function deleteSectionAction($sectionId){
		
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
		if($postData['section'] == 0){
			$section = new Section();
		} else {
			$section = $em->find('AppBundle\Entity\Section', $postData['section']);
			
			if(!$section){
				return $this->returnForbiddenResponse("Section ".$postData['section']." does not exist");
			}
		}
		
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
		
		if($postData['section'] == 0){
			# set the teacher to the person who made the section TODO
			if($course->is_contest){
				$role = $em->getRepository('AppBundle\Entity\Role')->findOneBy(array('role_name' => 'Judges'));
			} else {
				$role = $em->getRepository('AppBundle\Entity\Role')->findOneBy(array('role_name' => 'Teaches'));
			}
					
			$usr = new UserSectionRole($user, $section, $role);
			$em->persist($usr);			
		
		} else {
			# remove all the previous students before the "edit" if there was one
			$role = $em->getRepository('AppBundle\Entity\Role')->findOneBy(array('role_name' => 'Takes'));
			
			$builder = $em->createQueryBuilder();
			$builder->select('u')
				  ->from('AppBundle\Entity\UserSectionRole', 'u')
				  ->where('u.section = ?1')
				  ->andWhere('u.role = ?2')
				  ->setParameter(1, $section)
				  ->setParameter(2, $role);
			$query = $builder->getQuery();
			$current_student_roles = $query->getResult();
			
			foreach($current_student_roles as $student_role){
				$em->remove($student_role);
			}
			
			$em->flush();
		}
		
		# add the students from the students array
		$students = array_unique(json_decode($postData['students']));

		
		$role = $em->getRepository('AppBundle\Entity\Role')->findOneBy(array('role_name' => 'Takes'));
		foreach ($students as $student) {

			if ($student != "") {
				$stud_user = $em->getRepository('AppBundle\Entity\User')->findOneBy(array('email' => $student));

				if(!$stud_user){
					continue;
				}
				
				$usr = new UserSectionRole($stud_user, $section, $role);
				$em->persist($usr);
			}
		}
		
		$em->flush();
		
		# redirect to the section page
		$url = $this->generateUrl('section', ['sectionId' => $section->id]);		
		
		$response = new Response(json_encode(array('redirect_url' => $url, 'students' => $students)));
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
	*/
}
