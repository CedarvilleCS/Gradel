<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Entity\Course;
use AppBundle\Entity\UserSectionRole;
use AppBundle\Entity\Section;
use AppBundle\Entity\Assignment;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Psr\Log\LoggerInterface;


class AssignmentController extends Controller
{
    public function assignmentAction($userId, $sectionId, $assignmentId, $problemId) {

      $em = $this->getDoctrine()->getManager();

      $builder = $em->createQueryBuilder();
      $builder->select('assignment')
              ->from('AppBundle\Entity\Assignment assignment')
              ->where('assignment.id = :id')
              ->setParameter("id", $assignmentId);
      $query = $builder->getQuery();
      $assignment = $query->getSingleResult();

      foreach ($assignment->problems as $problem) {
        echo $problem->name . "<br/>";
      }

      $currentProblem = array_filter($assignment->problems, function($k, $v) {
        echo "stuff <br/>";
        echo $k . "<br/>";
        return $k->id == $problemId;
      }, ARRAY_FILTER_USE_BOTH);



      echo $problemId;

      $currentProblemDescription = stream_get_contents($currentProblem->description);
      return $this->render('default/assignment/index.html.twig', [
        'assignment' => $assignment,
        'currentProblem' => $currentProblem,
        'currentProblemDescription' => $currentProblemDescription,
      ]);
    }
}

?>
