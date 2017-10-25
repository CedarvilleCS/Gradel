<?php

namespace AppBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\Submission;


use AppBundle\Entity\Role;
use AppBundle\Entity\User;
use AppBundle\Entity\Team;
use AppBundle\Entity\Course;
use AppBundle\Entity\Section;
use AppBundle\Entity\Assignment;
use AppBundle\Entity\Problem;
use AppBundle\Entity\UserSectionRole;
use AppBundle\Entity\Testcase;
use AppBundle\Entity\Language;
use AppBundle\Entity\Gradingmethod;
use AppBundle\Entity\Filetype;
use AppBundle\Entity\Feedback;
use AppBundle\Entity\TestcaseResult;

use Psr\Log\LoggerInterface;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\Response;


class ProblemsController extends Controller {

    public function problemsAction($assignmentId=1, $problemId=1) {

      // $userId, $sectionId, $assignmentId, $problemId

      $em = $this->getDoctrine()->getManager();

      $builder = $em->createQueryBuilder();
      $builder->select('assignment')
              ->from('AppBundle\Entity\Assignment assignment')
              ->where('assignment.id = :id')
              ->setParameter("id", $assignmentId);
      $query = $builder->getQuery();
      $assignment = $query->getSingleResult();

      $currentProblem = [];

        foreach ($assignment->problems as $problem) {
        	if ($problem->id == $problemId) {
      			$currentProblem[] = $problem;
          }
    		}

      echo $currentProblem->name;
      $currentProblemDescription = stream_get_contents($currentProblem[0]->description);

      return $this->render('courses/assignments/problems/index.html.twig', [
              'project_id' => $assignmentId,
              'problem_id' => $problemId,
              'assignment' => $assignment,
              'currentProblem' => $currentProblem[0],
              'currentProblemDescription' => $currentProblemDescription,
      ]);
    }
}

?>
