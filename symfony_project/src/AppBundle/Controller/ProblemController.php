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
use Symfony\Component\HttpFoundation\JsonResponse;


class ProblemController extends Controller {


    public function newAction($sectionId, $assignmentId) {

      return $this->render('problem/new.html.twig', [
        'sectionId' => $sectionId,
        'assignmentId' => $assignmentId,
      ]);
    }

	public function insertAction($assignmentId, $name, $description, $weight, $time_limit) {
      $em = $this->getDoctrine()->getManager();
      $user = $this->get('security.token_storage')->getToken()->getUser();

      $assignment = $em->find("AppBundle\Entity\Assignment", $assignmentId);
      $problemGradingMethod = $em->find("AppBundle\Entity\ProblemGradingMethod", 1);

      $problem = new Problem();
      $problem->assignment = $assignment;
      $problem->gradingmethod = $problemGradingMethod;
      $problem->name = $name;
      $problem->description = $description;
      $problem->weight = $weight;
      $problem->is_extra_credit = false;
      $problem->time_limit = $time_limit;

      $em->persist($problem);
      $em->flush();

      return new JsonResponse(array('problemId' => $problem->id));
    }

    public function editAction() {

      return $this->render('problem/edit.html.twig', [

      ]);
    }

	public function resultAction($submission_id) {

		$em = $this->getDoctrine()->getManager();

		$submission = $em->find("AppBundle\Entity\Submission", $submission_id);

		if(!submission){
			echo "SUBMISSION DOES NOT EXIST";
			die();
		}

		$user = $this->get('security.token_storage')->getToken()->getUser();
		if(!get_class($user)){
			die("USER DOES NOT EXIST!");
		}

		$compiler_output = stream_get_contents($submission->compiler_output);
		$submission_file = stream_get_contents($submission->submitted_file);

		foreach($submission->testcaseresults as $tc){

			$output["std_output"] = stream_get_contents($tc->std_output);
			$output["runtime_output"] = stream_get_contents($tc->runtime_output);
			$output["time_output"] = $tc->execution_time;
			$tc_output[] = $output;
		}

		# get the usersectionrole
		$qb_usr = $em->createQueryBuilder();
		$qb_usr->select('usr')
			->from('AppBundle\Entity\UserSectionRole', 'usr')
			->where('usr.user = ?1')
			->andWhere('usr.section = ?2')
			->setParameter(1, $user)
			->setParameter(2, $submission->problem->assignment->section);

		$usr_query = $qb_usr->getQuery();
		$usersectionrole = $usr_query->getOneOrNullResult();

        return $this->render('problem/result.html.twig', [
			'submission' => $submission,
			'problem' => $submission->problem,
			'grader' => new Grader($em),
			'usersectionrole' => $usersectionrole,
			'testcases_output' => $tc_output,
			'compiler_output' => $compiler_output,
			'submission_file' => $submission_file,
			'result_page' => true,
        ]);
	}
}

?>
