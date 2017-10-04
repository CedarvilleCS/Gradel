<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Entity\Course;
use AppBundle\Entity\UserSectionRole;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request) {
		
		$em = $this->getDoctrine()->getManager();
		$query = $em->createQuery('SELECT u FROM AppBundle\Entity\User u WHERE 1=1');
		$users = $query->getResult();
		
		$query = $em->createQuery('SELECT c FROM AppBundle\Entity\Course c WHERE 1=1');
		$courses = $query->getResult();
		
		$query = $em->createQuery('SELECT usr FROM AppBundle\Entity\UserSectionRole usr WHERE usr.section=22');
		$course = $query->getResult();
		
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
			'users' => $users,
			'courses' => $courses,
			'course' => $course,
        ]);
	}
	
	/**
	* @Route("/submit", name="submit")
	*/
	public function submitAction() {
		return $this->render('default/submit.html.twig');
	}
}
