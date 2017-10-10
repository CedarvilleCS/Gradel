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

	
	$auth0 = new Auth0([
 	 'domain' => 'gradel.auth0.com',
 	 'client_id' => '2Ea1x0z0nSMqq3WWBs24pnzZFcqfA567',
 	 'client_secret' => 'T5ZhU-bvX5RlTAigtWQIyRe4x_nfvDIxV8FylaotMmI0RI-WLyRt4fgMXMG4wpEH',
 	 'redirect_uri' => 'http://joseph.cedarville.edu/gradel_dev/wolf/gradel/symfony_project/web/account',
 	 'audience' => 'https://gradel.auth0.com/userinfo',
  	'persist_id_token' => true,
 	 'persist_access_token' => true,
 	 'persist_refresh_token' => true,
	]);
	$userInfo = $auth0->getUser();
	if (!$userInfo) {
	    echo json_encode("sad");
	    // redirect to Login
	} else {
	    echo json_encode("He1y");
	    // Say hello to $userInfo['name']
	    // print logout button
	}	
		//$em = $this->getDoctrine()->getManager();
		//$new_user = new User($userName, "Wolf", "", new \DateTime("now"), "");
		//$em->persist($new_user);
		//echo json_encode("He1y");
		//echo json_encode($userName);
		//return $this->render('default/account/index.html.twig');
	}
	

}
