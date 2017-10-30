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


class LoginController extends Controller
{
    public function indexAction(Request $request, LoggerInterface $logger) {
      $logger->info("This should output somewhere!");

	  $usr= $this->get('security.token_storage')->getToken()->getUser();

	  if(get_class($usr)){
		$name = $usr->getFirstName();
		$name = $usr->setFirstName($name);
		return $this->redirectToRoute('account');
	  }

      return $this->render('login/index.html.twig', [
		  'name' => $name,
      ]);
    }
}

?>