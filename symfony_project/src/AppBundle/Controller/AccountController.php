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


class AccountController extends Controller
{
    public function indexAction(Request $request, LoggerInterface $logger) {
      $logger->info("This should output somewhere!");
	  
	  $usr= $this->get('security.token_storage')->getToken()->getUser();
	  
	  if(get_class($usr)){
		
		$userId = $usr->getID();
		$name = $usr->getFirstName();
		$name = $usr->setFirstName($name);
	  }
	  $em = $this->getDoctrine()->getManager();
      # get the user section role entities using the user entity as the where
      $b = $em->createQueryBuilder();
      $b->select('usr')
          ->from('AppBundle\Entity\UserSectionRole', 'usr')
          ->where('usr.user = ?1')
          ->setParameter(1, $userId);

      $q = $b->getQuery();
      $coursePerson = $q->getResult();

      $logger->info("WHAT UP FOOL");
          // replace this example code with whatever you need
        return $this->render('default/account/index.html.twig', [
              'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
  			      'coursePerson' => $coursePerson,
              'userId' => $userId, 'name' => $name
          ]);

    }

}

?>