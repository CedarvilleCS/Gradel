<?php

namespace AppBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SubmitController extends Controller
{
 
  public function submitAction($project_id=1) {
    return $this->render('courses/assignments/submit/index.html.twig', [
			'project_id' => $project_id,
    ]);
  }
}

?>
