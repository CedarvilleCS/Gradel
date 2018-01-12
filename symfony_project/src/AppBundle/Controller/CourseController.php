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

		if(!$user->hasRole("ROLE_SUPER") && !$user->hasRole("ROLE_ADMIN")){
			
			die("YOU DO NOT HAVE PERMISSION TO SEE THIS PAGE!");
		}
		
		$courses = $em->getRepository("AppBundle\Entity\Course")->findAll();
		
		usort($courses, array('AppBundle\Entity\Course', 'cmp'));
		
		$deleted_courses = [];
		$open_courses = [];
		
		foreach($courses as $cs){
			if($cs->is_deleted){
				$deleted_courses[] = $cs;
			} else {
				$open_courses[] = $cs;
			}
		}
		
		return $this->render('course/index.html.twig', [
			'courses' => $open_courses,
			'deleted_courses' => $deleted_courses,
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
		
		if(isset($courseId) && $courseId > 0){
			$course = $em->find('AppBundle\Entity\Course', $courseId);
		}
		
		$courses = $em->getRepository("AppBundle\Entity\Course")->findAll();
		
		$deleted_courses = [];
		$open_courses = [];
		
		foreach($courses as $cs){
			if($cs->is_deleted){
				$deleted_courses[] = $cs;
			} else {
				$open_courses[] = $cs;
			}
		}
		
		return $this->render('course/edit.html.twig', [
			'course' => $course,
			'courses' => $open_courses,
			'deleted_courses' => $deleted_courses,
		]);
    }
	
	public function deleteCourseAction($courseId){
		
		$em = $this->getDoctrine()->getManager();
		
		# validate the current user
		$user = $this->get('security.token_storage')->getToken()->getUser();
		if(!$user){			
			die("USER DOES NOT EXIST");
		}
		
		# only super users and admins can make/edit a course
		if(!$user->hasRole("ROLE_SUPER") && !$user->hasRole("ROLE_ADMIN")){			
			die("YOU DO NOT HAVE PERMISSION TO DELETE A COURSE");
		}
		
		
		if(!isset($courseId) || !($courseId > 0)){
			die("COURSE ID WAS NOT PROVIDED PROPERLY");
		}
		
		$course = $em->find('AppBundle\Entity\Course', $courseId);
		
		if(!$course){
			die("COURSE DOES NOT EXIST");
		}
		
		$course->is_deleted = !$course->is_deleted;
		
		if($course->is_deleted){
			foreach($course->sections as $section){
				$section->is_deleted = $course->is_deleted;;
			}
		}
		
		$em->flush();
		
		return $this->redirectToRoute('courses');
	}
	
	public function modifyPostAction(Request $request){
		
		$em = $this->getDoctrine()->getManager();
		
		# validate the current user
		$user = $this->get('security.token_storage')->getToken()->getUser();
		if(!$user){			
			return $this->returnForbiddenResponse("You are not a user.");
		}
		
		# only super users and admins can make/edit a course
		if(!$user->hasRole("ROLE_SUPER") && !$user->hasRole("ROLE_ADMIN")){			
			return $this->returnForbiddenResponse("You do not have permission to make a section.");
		}
		
		# see which fields were included
		$postData = $request->request->all();
		
		# check mandatory fields
		if(!isset($postData['name']) && !isset($postData['code']) && !isset($postData['description'])){
			return $this->returnForbiddenResponse("Not every required field is provided.");
		}
		
		# create new assignment
		if($postData['course'] == 0){
			$course = new Course();		
			$em->persist($course);
		} else {
			
			if(!isset($postData['course']) || !($postData['course'] > 0)){
				return $this->returnForbiddenResponse("Course id was not formatted properly");
			}
			
			$course = $em->find('AppBundle\Entity\Course', $postData['course']);
			
			if(!$course){
				return $this->returnForbiddenResponse("Course with id ".$postData['course']." does not exist.");
			}			
		}
		
		# set necessary fields
		$course->name = trim($postData['name']);
		$course->code = trim($postData['code']);
		$course->description = trim($postData['description']);
		
		# set contest
		if(isset($postData["is_contest"]) && $postData["is_contest"] == "true"){
			$course->is_contest = true;
		} else {			
			$course->is_contest = false;
		}
		
		# set currently unused fields
		$course->is_deleted = false;
		$course->is_public = false;
		
		$em->persist($course);
		$em->flush();
		
		# redirect to the section page
		$url = $this->generateUrl('courses');		
		
		$response = new Response(json_encode(array('redirect_url' => $url, 'post_data' => $postData, 'course' => $course)));
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
