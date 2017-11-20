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
    echo json_decode("hi");
      $em = $this->getDoctrine()->getManager();
      $user = $this->get('security.token_storage')->getToken()->getUser();
      $post_data = $request->request->all();

      $problem = $em->find("AppBundle\Entity\Problem", $post_data['problemId']);

      $input = $post_data['input'];
      $output = $post_data['output'];
      $weight = $post_data['weight'];
      $short_feedback = $post_data['short_response'];
      $long_feedback = $post_data['long_response'];

      $feedback = new Feedback();
      $feedback->short_response = $short_feedback;
      $feedback->long_response = $long_feedback;

      $em->persist($feedback);
      $em->flush();

      $testcase = new Testcase();

      $testcase->problem = $problem;
      $testcase->feedback = $feedback;
  	  $testcase->seq_num = 0; // Not yet being used
  	  $testcase->input = $input;
  	  $testcase->correct_output = $output;
  	  $testcase->weight = $weight;
      $testcase->is_extra_credit = false;
      //
      $em->persist($testcase);
      $em->flush();

      return new JsonResponse(array("input" => $input));
    }

    public function editAction() {

      return $this->render('problem/edit.html.twig', [

      ]);
    }


}

?>
