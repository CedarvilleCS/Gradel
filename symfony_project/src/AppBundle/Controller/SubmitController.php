<?php

namespace AppBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
// use Symfony\Component\HttpFoundation\Request;

class SubmitController extends Controller
{
 
  public function submitAction() {
    return $this->render('submit/index.html.twig');
  }
}

?>
