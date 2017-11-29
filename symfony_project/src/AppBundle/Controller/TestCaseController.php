<?php

namespace AppBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use AppBundle\Entity\Submission;
use AppBundle\Entity\Problem;
use AppBundle\Entity\ProblemLanguage;
use AppBundle\Entity\UserSectionRole;
use AppBundle\Entity\Feedback;
use AppBundle\Entity\Testcase;

use AppBundle\Utils\Grader;
use AppBundle\Utils\TestCaseCreator;


use Psr\Log\LoggerInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;


class TestCaseController extends Controller {


    public function newAction($sectionId, $assignmentId) {

      return $this->render('problem/new.html.twig', [
        'sectionId' => $sectionId,
        'assignmentId' => $assignmentId,
      ]);
    }

	public function insertAction(Request $request) {
   
		$em = $this->getDoctrine()->getManager();
		$user = $this->get('security.token_storage')->getToken()->getUser();
		
		$postData = $request->request->all();

		# get the problem from the post
		$problem = $em->find("AppBundle\Entity\Problem", $postData['problemId']);
	  
		if(!$problem){
			return $this->returnForbiddenResponse("The problem with provided id does not exist!");
		}
		
		# make sure this user can make a testcase
		$grader = new Grader($em);
		if(!$user->hasRole("ROLE_SUPER") && !$grader->isTeaching($user, $problem->assignment->section)){
			return $this->returnForbiddenResponse("You are not allowed to create testcases for this problem");
		}

		# build the testcase 
		$response = TestCaseCreator::makeTestCase($em, $problem, $postData);
		
		# check what the makeTestCase returns
		if(!$response->problem){
			return $response;
		} else{
			$testcase = $response;
		}

		$em->persist($testcase);
		$em->flush();

		$response = new Response(json_encode(array('testcase_id'=>$testcase->id)));
		$response->headers->set('Content-Type', 'application/json');
		$response->setStatusCode(Response::HTTP_OK);

		return $response;
    }

    public function editAction() {

      return $this->render('problem/edit.html.twig', [

      ]);
    }
	
	private function returnForbiddenResponse($message){		
		$response = new Response($message);
		$response->setStatusCode(Response::HTTP_FORBIDDEN);
		return $response;
	}

}

?>
