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
    public function indexAction(Request $request, LoggerInterface $logger) {
      $logger->info("This should output somewhere!");
	  
	  $usr= $this->get('security.token_storage')->getToken()->getUser();
	  
	  if(get_class($usr)){
		$name = $usr->getUsername();
	  }
	  
      return $this->render('default/index.html.twig', [
		  'username' => $name,
      ]);
    }

    public function accountAction(LoggerInterface $logger, $userId) {
      $em = $this->getDoctrine()->getManager();
      # get the user entity
      $b2 = $em->createQueryBuilder();
      $b2->select('u')
          ->from('AppBundle\Entity\User', 'u')
          ->where('u.id = ?1')
          ->setParameter(1, $userId);

      $q2 = $b2->getQuery();
      $keith = $q2->getSingleResult();

      # get the user section role entities using the user entity as the where
      $b = $em->createQueryBuilder();
      $b->select('usr')
          ->from('AppBundle\Entity\UserSectionRole', 'usr')
          ->where('usr.user = ?1')
          ->setParameter(1, $keith);

      $q = $b->getQuery();
      $coursePerson = $q->getResult();

      $logger->info("WHAT UP FOOL");
          // replace this example code with whatever you need
          return $this->render('default/account/index.html.twig', [
              'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
  			      'coursePerson' => $coursePerson,
              'userId' => $userId
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

?>
