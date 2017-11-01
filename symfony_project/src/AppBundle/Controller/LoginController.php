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
    public function indexAction(Request $request) {

		$user= $this->get('security.token_storage')->getToken()->getUser();

		if(get_class($user)){
			$name = $user->getFirstName();
			return $this->redirectToRoute('account');
		}

		return $this->render('login/index.html.twig', [
			'name' => $name,
		]);
    }
}

?>
