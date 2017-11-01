<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Entity\Course;
use AppBundle\Entity\UserSectionRole;
use AppBundle\Entity\Section;
use AppBundle\Entity\Assignment;

use \DateTime;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;


use Psr\Log\LoggerInterface;


class AssignmentController extends Controller
{

    public function newAction($userId, $sectionId) {

      return $this->render('default/assignment/new.html.twig', [
        "userId" => $userId,
        "sectionId" => $sectionId,
      ]);
    }

    public function insertAction($sectionId, $name, $description) {
      $em = $this->getDoctrine()->getManager();
      $user = $this->get('security.token_storage')->getToken()->getUser();

      $assignment = new Assignment();
      $section = $em->find('AppBundle\Entity\Section', $sectionId);

      $gradingmethod = $em->find('AppBundle\Entity\Gradingmethod', 1);

      $assignment->name = $name;
      $assignment->description = $description;
      $assignment->section = $section;
      $assignment->start_time = new DateTime("now");
      $assignment->end_time = new DateTime("2050-01-01");
      $assignment->cutoff_time = new DateTime("2050-01-01");
      $assignment->weight = 0;
      $assignment->is_extra_credit = false;
      $assignment->gradingmethod = $gradingmethod;


      $em->persist($assignment);
      $em->flush();

      return new RedirectResponse($this->generateUrl('assignment_edit', array('userId' => $user->id, 'sectionId' => $sectionId, 'assignmentId' => $assignment->id)));

    }

    public function editAction($userId, $sectionId, $assignmentId) {

      $em = $this->getDoctrine()->getManager();

      $assignment = $em->find('AppBundle\Entity\Assignment', $assignmentId);

      return $this->render('default/assignment/edit.html.twig', [
        "userId" => $userId,
        "sectionId" => $sectionId,
        "assignmentId" => $assignmentId,
        "assignment" => $assignment,
        "description" => stream_get_contents($assignment->description),
      ]);
    }

    public function editQueryAction($sectionId, $assignmentId, $name, $description) {
      $em = $this->getDoctrine()->getManager();

      $user = $this->get('security.token_storage')->getToken()->getUser();


      $assignment = $em->find('AppBundle\Entity\Assignment', $assignmentId);
      $assignment->name = $name;
      $assignment->description = $description;
      // $assignment->start_time = new DateTime("now");
      // $assignment->end_time = new DateTime("2050-01-01");
      // $assignment->cutoff_time = new DateTime("2050-01-01");
      // $assignment->weight = 0;
      // $assignment->is_extra_credit = false;
      // $assignment->gradingmethod = $gradingmethod;

      $em->persist($assignment);
      $em->flush();

      return new RedirectResponse($this->generateUrl('assignment', array('userId' => $user->id, 'sectionId' => $sectionId, 'assignmentId' => $assignment->id)));
    }
}

?>
