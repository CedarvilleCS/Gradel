<?php

namespace AppBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
// use Symfony\Component\HttpFoundation\Request;

class SubmitController extends Controller
{
 
  public function submitAction($problem_id=1) {
    return $this->render('courses/assignments/submit/index.html.twig', [
			'problem_id' => $problem_id,
    ]);
  }
}

?>
