<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Entity\Course;
use AppBundle\Entity\UserSectionRole;
use AppBundle\Entity\Assignment;

use \DateTime;

use Auth0\SDK\Auth0;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Psr\Log\LoggerInterface;

class HomeController extends Controller {
	
    public function indexAction(Request $request, LoggerInterface $logger) {

		$em = $this->getDoctrine()->getManager();
	  
		$user = $this->get('security.token_storage')->getToken()->getUser();
	  
	  
		if(!get_class($user)){
			die("USER DOES NOT EXIST!");		  
		}
	  
		# get the user section role entities using the user entity as the where
		$qb_usr = $em->createQueryBuilder();
		$qb_usr->select('usr')
			->from('AppBundle\Entity\UserSectionRole', 'usr')
			->where('usr.user = ?1')
			->setParameter(1, $user->id);

		$usr_query = $qb_usr->getQuery();
		$usersectionroles = $usr_query->getResult();
		
		$sections = [];
		foreach($usersectionroles as $usr){
			$sections[] = $usr->section->id;
		}
		
		# get assignments sorted by due date
		$qb_asgn = $em->createQueryBuilder();
		$qb_asgn->select('a')
			->from('AppBundle\Entity\Assignment', 'a')
			->where('a.section IN (?1)')
			->andWhere('a.end_time > (?2)')
			->setParameter(1, $sections)
			->setParameter(2, new DateTime())
			->orderBy('a.end_time', 'ASC');
			
		$asgn_query = $qb_asgn->getQuery();		

		$assignments = $asgn_query->getResult();	

		// replace this example code with whatever you need
		return $this->render('default/account/index.html.twig', [
			'usersectionroles' => $usersectionroles,
			'assignments' => $assignments,
			'user' => $user,
			'currTime' => new DateTime()
		]);

    }

}

?>