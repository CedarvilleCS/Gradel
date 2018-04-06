<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Entity\Course;
use AppBundle\Entity\UserSectionRole;
use AppBundle\Entity\Section;
use AppBundle\Entity\Assignment;
use AppBundle\Entity\Team;
use AppBundle\Entity\Trial;

use AppBundle\Utils\Grader;
use AppBundle\Utils\Uploader;

use Doctrine\Common\Collections\ArrayCollection;

use \DateTime;
use \DateInterval;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

use Psr\Log\LoggerInterface;

class UsersController extends Controller {

	public function usersAction() {

		$em = $this->getDoctrine()->getManager();
		$usr = $em->getRepository("AppBundle\Entity\UserSectionRole")->findBy([], ['user' => 'ASC']);
		$roles = $em->getRepository("AppBundle\Entity\Role")->findAll();

		$client = $this->get('security.token_storage')->getToken()->getUser();
		if(!($client->hasRole("ROLE_SUPER") || $client->hasRole("ROLE_ADMIN"))) {
			die("you shall not pass");
		}
		
		$user = $this->get('security.token_storage')->getToken()->getUser();  	  
		if(!get_class($user)){
			die("USER DOES NOT EXIST!");		  
		}

		if(!$user->hasRole("ROLE_SUPER")){
			die("YOU'RE NOT ALLOWED TO BE HERE!");
		}
		
		$userRoles = array();
		foreach ($usr as $u) {
			$userRoles[$u->user->id] = array(
				"username" => $u->user->getUsername(),
				"first" => $u->user->getFirstName(),
				"last" => $u->user->getLastName(),
				"roles" => $u->user->getRoles()
			);
		}

        return $this->render('users/index.html.twig', [
			'users' => $usr,
			'userRoles' => json_encode($userRoles),
			'roles' => $roles
		]);

		
    }

	public function editAction(Request $request) {
		$client = $this->get('security.token_storage')->getToken()->getUser();
		if(!($client->hasRole("ROLE_SUPER") || $client->hasRole("ROLE_ADMIN"))) {
			die("you shall not pass");
		}

		$users = $request->request->all();
		$em = $this->getDoctrine()->getManager();

		$blash = array();
		foreach ($users as $key => $u) {
			$user = $em->find("AppBundle\Entity\User", $key);
			if ($user->getFirstName() != $u["first"]) {
				$user->setFirstName($u["first"]);
			}
			if ($user->getLastName() != $u["last"]) {
				$user->setLastName($u["last"]);
			}
			if ($user->getUsername() != $u["username"]) {
				$user->setUsername($u["username"]);
			}
			foreach ($u["roles"] as $r) {
				if (
					!in_array($r, $user->getRoles()) 
					&& ($r == "ROLE_SUPER" || $r =="ROLE_ADMIN")
				) {
					$user->addRole($r);
				}
			}
			foreach ($user->getRoles() as $r) {
				if (!in_array($r, $u["roles"])) {
					$user->removeRole($r);
				}
			}
			$em->persist($user);
		}
		$em->flush();
		$em = $this->getDoctrine()->getManager();
		return new JsonResponse(json_encode($blash));
	}
}

?>
