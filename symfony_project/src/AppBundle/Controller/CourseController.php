<?php

namespace AppBundle\Controller;

use AppBundle\Constants;

use AppBundle\Entity\User;
use AppBundle\Entity\Course;
use AppBundle\Entity\Role;
use AppBundle\Entity\Section;

use AppBundle\Service\CourseService;
use AppBundle\Service\UserService;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Psr\Log\LoggerInterface;

class CourseController extends Controller {
	private $logger;
	private $courseService;
	private $userService;

	public function __construct(LoggerInterface $logger,
	                            CourseService $courseService,
	                            UserService $userService) {
		$this->logger = $logger;
		$this->courseService = $courseService;
		$this->userService = $userService;
	}

    public function coursesAction() {
		$user = $this->userService->getCurrentUser();

		if (!get_class($user)) {
			return $this->returnForbiddenResponse("USER DOES NOT EXIST");
		}

		if (!$user->hasRole(CONSTANTS::SUPER_ROLE) && !$user->hasRole(CONSTANTS::ADMIN_ROLE)) {
			return $this->returnForbiddenResponse("YOU DO NOT HAVE PERMISSION TO SEE THIS PAGE");
		}

		$courses = $this->courseService->getAll();
		
		usort($courses, array("AppBundle\Entity\Course", "cmp"));

		$deletedCourses = [];
		$openCourses = [];
		
		foreach ($courses as $course) {
			if ($course->is_deleted) {
				$deletedCourses[] = $course;
			} else {
				$openCourses[] = $course;
			}
		}
		
		return $this->render("course/index.html.twig", [
			"courses" => $openCourses,
			"deleted_courses" => $deletedCourses
		]);
    }

    public function editCourseAction($courseId) {
		$user = $this->userService->getCurrentUser();
		if (!get_class($user)) {
			return $this->returnForbiddenResponse("USER DOES NOT EXIST");
		}
		
		if (!$user->hasRole(CONSTANTS::SUPER_ROLE) && !$user->hasRole(CONSTANTS::ADMIN_ROLE)) {
			return $this->returnForbiddenResponse("YOU DO NOT HAVE PERMISSION TO SEE THIS PAGE"); 
		}
		
		$editedCourse = [];
		if (isset($courseId) && $courseId > 0) {
			$editedCourse = $this->courseService->getCourseById($courseId);
		}
		
		$courses = $this->courseService->getAll();

		$deletedCourses = [];
		$openCourses = [];
		
		foreach ($courses as $course) {
			if ($course->is_deleted) {
				$deletedCourses[] = $course;
			} else {
				$openCourses[] = $course;
			}
		}
		
		return $this->render("course/edit.html.twig", [
			"course" => $editedCourse,
			"courses" => $openCourses,
			"deleted_courses" => $deletedCourses,
		]);
    }
	
	public function deleteCourseAction($courseId) {
		$user = $this->userService->getCurrentUser();
		if (!get_class($user)) {
			return $this->returnForbiddenResponse("USER DOES NOT EXIST");
		}
		
		/* only super users and admins can make/edit a course */
		if (!$user->hasRole(CONSTANTS::SUPER_ROLE) && !$user->hasRole(CONSTANTS::ADMIN_ROLE)) {
			return $this->returnForbiddenResponse("YOU DO NOT HAVE PERMISSION TO DELETE A COURSE");
		}
		
		if (!isset($courseId) || !($courseId > 0)) {
			return $this->returnForbiddenResponse("COURSE ID WAS NOT PROVIDED PROPERLY");
		}
		
		$course = $this->courseService->getCourseById($courseId);
		
		if (!$course) {
			return $this->returnForbiddenResponse("COURSE ".$courseId." DOES NOT EXIST");
		}
		
		$course->is_deleted = !$course->is_deleted;
		
		if ($course->is_deleted) {
			foreach ($course->sections as $section) {
				$section->is_deleted = $course->is_deleted;
			}
		}
		$this->courseService->insertCourse($course);
		
		return $this->redirectToRoute("courses");
	}
	
	public function modifyPostAction(Request $request) {
		$user = $this->userService->getCurrentUser();
		if (!get_class($user)) {
			return $this->returnForbiddenResponse("USER DOES NOT EXIST");
		}
		
		/* Only super users and admins can make/edit a course */
		if (!$user->hasRole(CONSTANTS::SUPER_ROLE) && !$user->hasRole(CONSTANTS::ADMIN_ROLE)){			
			return $this->returnForbiddenResponse("YOU DO NOT HAVE PERMISSION TO MAKE A COURSE");
		}
		
		/* See which fields were included */
		$postData = $request->request->all();
		
		/* Check mandatory fields */
		if (!isset($postData["name"]) || 
			!isset($postData["code"]) || 
			!isset($postData["description"])) {
			return $this->returnForbiddenResponse("Not every required field is provided.");
		}
		
		$courseId = $postData["course"];
		/* Create new assignment */
		if ($courseId == 0) {
			$course = $this->courseService->createEmptyCourse();
			$this->courseService->insertCourse($course);
		} else {
			if (!isset($courseId) ||
			    !($courseId > 0)) {
				return $this->returnForbiddenResponse("COURSE ID ".$courseId." WAS NOT PROVIDED OR FORMATTED CORRECTLY");
			}
			
			$course = $this->courseService->getCourseById($courseId);
			
			if (!$course) {
				return $this->returnForbiddenResponse("COURSE ".$courseId." DOES NOT EXIST");
			}
		}
		
		/* set necessary fields */
		$course->name = trim($postData["name"]);
		$course->code = trim($postData["code"]);
		$course->description = trim($postData["description"]);
		
		/* Set contest */
		if(isset($postData["is_contest"]) && trim($postData["is_contest"]) == "true"){
			$course->is_contest = true;
		} else {			
			$course->is_contest = false;
		}
		
		/* Set currently unused fields */
		$course->is_deleted = false;
		$course->is_public = false;
		
		$this->courseService->insertCourse($course);
		
		/* Redirect to the section page */
		$url = $this->generateUrl("courses");
		
		$response = new Response(json_encode([
			"redirect_url" => $url, 
			"post_data" => $postData, 
			"course" => $course
		]));

		return $this->returnOkResponse($response);
	}	
		
	private function logError($message) {
		$errorMessage = "CourseController: ".$message;
		$this->logger->error($errorMessage);
		return $errorMessage;
	}
	
	private function returnForbiddenResponse($message){		
		$response = new Response($message);
		$response->setStatusCode(Response::HTTP_FORBIDDEN);
		$this->logError($message);
		return $response;
	}

	private function returnOkResponse($response) {
		$response->headers->set("Content-Type", "application/json");
		$response->setStatusCode(Response::HTTP_OK);
		return $response;
	}
}
