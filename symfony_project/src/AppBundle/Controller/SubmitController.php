<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SubmitController extends Controller
{
    /**
     * @Route("/submit", name="submit")
     */
    public function submitAction()
    {
        return $this->render('AppBundle:Submit:submit.html.twig', array(
            // ...
        ));
    }
}
?>