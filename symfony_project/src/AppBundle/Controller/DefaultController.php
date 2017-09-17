<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;

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
		
		
		$users2 = [];
		foreach($users as $user){
			
			$user2 = [];
			$user2['first_name'] = $user->getFirstName();
			$user2['last_name'] = $user->getLastName();
			
			$users2[] = $user2;
		}
		
		
		
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
			'users' => $users2,
        ]);
    }
}
