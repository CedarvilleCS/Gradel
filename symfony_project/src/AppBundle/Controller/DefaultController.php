<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Entity\Course;
use AppBundle\Entity\UserSectionRole;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Psr\Log\LoggerInterface;


class DefaultController extends Controller
{
    /**
     * @Route("/", name="index")
     */
    public function indexAction(LoggerInterface $logger) {

		// $em = $this->getDoctrine()->getManager();
		// $query = $em->createQuery('SELECT u FROM AppBundle\Entity\User u WHERE 1=1');
		// $users = $query->getResult();
    //
		// $query = $em->createQuery('SELECT c FROM AppBundle\Entity\Course c WHERE 1=1');
		// $courses = $query->getResult();
    //
		// $query = $em->createQuery('SELECT usr FROM AppBundle\Entity\UserSectionRole usr WHERE usr.section=22');
		// $course = $query->getResult();
    //
    //     // replace this example code with whatever you need
    //     return $this->render('default/index.html.twig', [
    //         'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
		// 	'users' => $users,
		// 	'courses' => $courses,
		// 	'course' => $course,
    //     ]);
      $logger->info("CHRIS'S LG");
      $logger->error(error);
      return $this->render('default/index.html.twig');
    }

    /**
    *
    *
    * @Route("/account", name="account")
    */
    public function accountAction(LoggerInterface $logger) {
      $logger->error("This is the beginning of indexAction");
      $em = $this->getDoctrine()->getManager();
  		$query = $em->createQuery('SELECT u FROM AppBundle\Entity\User u WHERE 1=1');
  		$users = $query->getResult();

  		$query = $em->createQuery('SELECT c FROM AppBundle\Entity\Course c WHERE 1=1');
  		$courses = $query->getResult();

  		$query = $em->createQuery('SELECT usr FROM AppBundle\Entity\UserSectionRole usr WHERE usr.section=22');
  		$course = $query->getResult();

      $logger->info("CHRIS'S LOG!!!!!!");
      $logger->info("USERS");
      $logger->info(json_encode($users));
      $logger->info("COURSES");
      $logger->info (json_encode($courses));
      $logger->info("COURSE");
      $logger->info(json_encode($course));

          // replace this example code with whatever you need
          return $this->render('default/account/index.html.twig', [
              'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
  			'users' => $users,
  			'courses' => $courses,
  			'course' => $course,
          ]);
      // return $this->render('default/account/index.html.twig');
    }
}
