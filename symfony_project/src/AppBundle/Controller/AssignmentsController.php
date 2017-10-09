<?php

namespace AppBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
// use Symfony\Component\HttpFoundation\Request;

class AssignmentsController extends Controller
{
 
  public function assignmentsAction() {
    return $this->render('courses/assignments/index.html.twig');
  }
}

?>
