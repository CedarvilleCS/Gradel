<?php

namespace AppBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
// use Symfony\Component\HttpFoundation\Request;

class CoursesController extends Controller
{
 
  public function coursesAction() {
    return $this->render('courses/index.html.twig');
  }
}

?>
