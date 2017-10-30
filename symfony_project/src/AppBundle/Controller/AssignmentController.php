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

    public function newAction($userId, $sectionId) {

      return $this->render('default/assignment/new.html.twig', [
        "userId" => $userId,
        "sectionId" => $sectionId,
      ]);
    }

    public function editAction($userId, $sectionId, $assignmentId) {

      return $this->render('default/assignment/edit.html.twig', [
        "userId" => $userId,
        "sectionId" => $sectionId,
        "assignmentId" => $assignmentId,
      ]);
    }
}

?>
