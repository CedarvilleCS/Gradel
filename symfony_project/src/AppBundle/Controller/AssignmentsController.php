<?php

namespace AppBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
// use Symfony\Component\HttpFoundation\Request;

class AssignmentsController extends Controller
{
 
  public function assignmentsAction($course_number='CS-1210') {
    return $this->render('courses/assignments/index.html.twig', [
			'course_number' => $course_number,
    ]);
  }
}

?>
