<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Entity\Course;
use AppBundle\Entity\UserSectionRole;

use Auth0\SDK\Auth0;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Psr\Log\LoggerInterface;


class DefaultController extends Controller
{
    public function indexAction(LoggerInterface $logger) {
      return $this->render('default/index.html.twig');
    }

    public function accountAction() {
      $em = $this->getDoctrine()->getManager();
  		$query = $em->createQuery('SELECT u FROM AppBundle\Entity\User u WHERE 1=1');
  		$users = $query->getResult();

  		$query = $em->createQuery('SELECT c FROM AppBundle\Entity\Course c WHERE 1=1');
  		$courses = $query->getResult();

          // replace this example code with whatever you need
          return $this->render('default/account/index.html.twig', [
              'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
  			'users' => $users,
  			'courses' => $courses,
          ]);
      // return $this->render('default/account/index.html.twig');
    }
	
	public function addUserAction($userName){	

	
		//$em = $this->getDoctrine()->getManager();
		//$new_user = new User($userName, "Wolf", "", new \DateTime("now"), "");
		//$em->persist($new_user);
		//echo json_encode("He1y");
		echo json_encode(yo, $userName);
		return $this->render('default/account/index.html.twig');
	}
	

}
