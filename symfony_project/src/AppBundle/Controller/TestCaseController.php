<?php

namespace AppBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use AppBundle\Entity\Submission;
use AppBundle\Entity\Problem;
use AppBundle\Entity\ProblemLanguage;
use AppBundle\Entity\UserSectionRole;

use AppBundle\Utils\Grader;

use Psr\Log\LoggerInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class TestCaseController extends Controller {

   
    public function newAction($sectionId, $assignmentId) {
		
      return $this->render('problem/new.html.twig', [
        'sectionId' => $sectionId,
        'assignmentId' => $assignmentId,
      ]);
    }

	public function insertAction($sectionId, $assignmentId, $problem_id, $feedback_id, $seq_num, $input, $correct_output, $weight) {
		echo json_decode("yo");
		die();
      $em = $this->getDoctrine()->getManager();
      $user = $this->get('security.token_storage')->getToken()->getUser();

      $testcase = new TestCase();
      $section = $em->find('AppBundle\Entity\Section', $sectionId);

      $gradingmethod = $em->find('AppBundle\Entity\AssignmentGradingMethod', 1);

      $testcase->problem_id = $problem_id;
      $testcase->feedback_id = $feedback_id;
	  $testcase->seq_num = $seq_num;
	  $testcase->input = $input;
	  $testcase->correct_output = $correct_output;
	  $testcase->weight = $weight;

      $em->persist($testcase);
      $em->flush();

      return new RedirectResponse($this->generateUrl('testcase', array('sectionId' => $sectionId, 'assignmentId' => $assignment->id)));
    }
	
    public function editAction() {

      return $this->render('problem/edit.html.twig', [

      ]);
    }
	
	
}

?>