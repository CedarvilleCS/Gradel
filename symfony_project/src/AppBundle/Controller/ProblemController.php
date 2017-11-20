<?php

namespace AppBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use AppBundle\Entity\Submission;
use AppBundle\Entity\Problem;
use AppBundle\Entity\ProblemLanguage;
use AppBundle\Entity\UserSectionRole;
use AppBundle\Entity\ProblemGradingMethod;

use AppBundle\Utils\Grader;

use Psr\Log\LoggerInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Doctrine\Common\Collections\ArrayCollection;

define("MULTIPLIER", '10000000000');



class ProblemController extends Controller {

    public function newAction($sectionId, $assignmentId) {
      $em = $this->getDoctrine()->getManager();
      $qb = $em->createQueryBuilder();

      $qb->select('l')
        ->from('AppBundle\Entity\Language', 'l')
        ->where('1 = 1');

      $languages = $qb->getQuery()->getResult();

      $qb = $em->createQueryBuilder();
      $qb->select('gm')
        ->from('AppBundle\Entity\ProblemGradingMethod', 'gm')
        ->where('1 = 1');

      $gradingMethods = $qb->getQuery()->getResult();


      return $this->render('problem/new.html.twig', [
        'languages' => $languages,
        'sectionId' => $sectionId,
        'assignmentId' => $assignmentId,
        'gradingMethods' => $gradingMethods,
      ]);
    }

	public function insertAction(Request $request) {
      $em = $this->getDoctrine()->getManager();
      $user = $this->get('security.token_storage')->getToken()->getUser();
      $post_data = $request->request->all();
      $errors = array();


      $languageArr = $post_data['languages'];
      if (sizeof($languageArr) == 0) {
        array_push($errors, "You must provide at least one language");
      }
      $assignment = $em->find("AppBundle\Entity\Assignment", $post_data['assignmentId']);

      $name = $post_data['name'];
      if ($name == "") {
        array_push($errors, "Name must be set");
      }

      $description = $post_data['description'];
      if ($description == "") {
        array_push($errors, "Description must be set");
      }
      $weight = $post_data['weight'];
      if (!is_numeric($weight) || ((int)$weight < 0)) {
        array_push($errors, "You must provide an integer weight greater than 0. You provided: " . $weight);
      }
      $is_extra_credit = $post_data['is_extra_credit'];
      if ($is_extra_credit !== "true" && $is_extra_credit !== "false") {
        array_push($errors, "You are trying to be malicious! Stop it! Extra Credit must be a boolean");
      }
      $time_limit = $post_data['time_limit'];
      if ($time_limit <= 0) {
        array_push($errors, "Time limit must be greater than 0!");
      }

      if (sizeof($errors) == 0) {

        $problem = new Problem();
        $problemGradingMethod = $em->find("AppBundle\Entity\ProblemGradingMethod", $post_data['grading_method']);

        $problem->assignment = $assignment;
        $problem->gradingmethod = $problemGradingMethod;
        $problem->name = $name;
        $problem->description = $description;
        $problem->weight = $weight;
        $problem->is_extra_credit = ($is_extra_credit == "true") ? 1 : 0;
        $problem->time_limit = $time_limit;

        $em->persist($problem);

        foreach ($languageArr as $language) {
          $l = $em->find("AppBundle\Entity\Language", $language);
          $problemLanguage = new ProblemLanguage();

          $problemLanguage->language = $l;
          $problemLanguage->problem = $problem;
          $em->persist($problemLanguage);
        }
      }
      $em->flush();

      return new JsonResponse(array("errors"=> $errors, "problemId"=> $problem->id));
    }

    public function editAction() {

      return $this->render('problem/edit.html.twig', [

      ]);
    }
	public function deleteAction($sectionId, $assignmentId, $problemId){

		$em = $this->getDoctrine()->getManager();

		$problem = $em->find('AppBundle\Entity\Problem', $problemId);
		if(!$problem){
			die("PROBLEM DOES NOT EXIST");
		}

		$user = $this->get('security.token_storage')->getToken()->getUser();
		if(!$user){
			die("USER DOES NOT EXIST");
		}

		# validate the user
		$grader = new Grader($em);
		if(!$user->hasRole("ROLE_SUPER") && !$user->hasRole("ROLE_ADMIN") && !$grader->isTeaching($user, $problem->assignment->section)){
			die("YOU ARE NOT ALLOWED TO DELETE THIS PROBLEM");
		}

		$em->remove($problem);
		$em->flush();
		return $this->redirectToRoute('assignment', ['sectionId' => $problem->assignment->section->id, 'assignmentId' => $problem->assignment->id]);
	}

	public function resultAction($submission_id) {

		$em = $this->getDoctrine()->getManager();
		$grader = new Grader($em);

		$submission = $em->find("AppBundle\Entity\Submission", $submission_id);

		if(!$submission){
			echo "SUBMISSION DOES NOT EXIST";
			die();
		}

		# get the user
		$user = $this->get('security.token_storage')->getToken()->getUser();
		if(!$user){
			die("USER DOES NOT EXIST!");
		}

		# make sure the user has permissions to view the submission result
		if($user->hasRole("ROLE_SUPER") && !$grader->isTeaching($user, $submission->problem->assignment->section) && !$grader->isOnTeam($user, $submission->problem->assignment, $submission->team)){
			echo "YOU ARE NOT ALLOWED TO VIEW THIS SUBMISSION";
			die();
		}

		# get all of the contents of the compiler and file
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


  // Then reduce any list of integer
  private function gcdArr($arr) {
    return array_reduce($arr, array($this, 'gcd'));
  }

  private function gcd ($a, $b) {
    return $b ? $this->gcd($b, $a % $b) : $a;
  }
}





?>
