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
		$name = $usr->getFirstName();
		return $this->redirectToRoute('account', array('name' => $name,));
	  }

      return $this->render('default/index.html.twig', [
		  'name' => $name,
      ]);
    }

}

?>
