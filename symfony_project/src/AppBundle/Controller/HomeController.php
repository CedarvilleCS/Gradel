<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Entity\Course;
use AppBundle\Entity\UserSectionRole;
use AppBundle\Entity\Assignment;

use AppBundle\Utils\Grader;

use \DateTime;
use \DateInterval;

use Psr\Log\LoggerInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends Controller {
	
    public function homeAction() {
		
	
		$em = $this->getDoctrine()->getManager();
	  
		$user = $this->get('security.token_storage')->getToken()->getUser();
	  	if(!get_class($user)){
			die("USER DOES NOT EXIST!");		  
		}
		
		# get all of the non-deleted sections
		$builder = $em->createQueryBuilder();
		$builder->select('s')
			->from('AppBundle\Entity\Section', 's')
			->where('s.is_deleted = false');
			//->andWhere('s.start_time < ?1')
			//->andWhere('s.end_time > ?2')
			//->setParameter(1, new DateTime())
			//->setParameter(2, new DateTime());
		
		$section_query = $builder->getQuery();
		$sections_active = $section_query->getResult();
	  
		# get the user section role entities using the user entity and not deleted sections
		$qb_usr = $em->createQueryBuilder();
		$qb_usr->select('usr')
			->from('AppBundle\Entity\UserSectionRole', 'usr')
			->where('usr.user = ?1')
			->andWhere('usr.section IN (?2)')
			->setParameter(1, $user)
			->setParameter(2, $sections_active);

		$usr_query = $qb_usr->getQuery();
		$usersectionroles = $usr_query->getResult();
		
		$sections = [];
		$sections_taking = [];
		$sections_teaching = [];
		foreach($usersectionroles as $usr){
			$sections[] = $usr->section->id;
			
			if($usr->role->role_name == 'Takes'){
				$sections_taking[] = $usr->section;
			} else if($usr->role->role_name == 'Teaches'){
				$sections_teaching[] = $usr->section;
			}
		}
		
		# get upcoming assignments sorted by due date
		$twoweeks_date = new DateTime();
		$twoweeks_date = $twoweeks_date->add(new DateInterval('P2W'));
		
		$qb_asgn = $em->createQueryBuilder();
		$qb_asgn->select('a')
			->from('AppBundle\Entity\Assignment', 'a')
			->where('a.section IN (?1)')
			->andWhere('a.end_time > (?2)')
			->andWhere('a.end_time < (?3)')
			->setParameter(1, $sections)
			->setParameter(2, new DateTime())
			->setParameter(3, $twoweeks_date)
			->orderBy('a.end_time', 'ASC');
			
		$asgn_query = $qb_asgn->getQuery();		
		$assignments = $asgn_query->getResult();	
		
		$qb_users = $em->createQueryBuilder();
		$qb_users->select('u')
			->from('AppBundle\Entity\User', 'u')
			->where('u != ?1')
			->setParameter(1, $user);
			
		$users_query = $qb_users->getQuery();
		$users = $users_query->getResult();	
		
		$grader = new Grader($em);
		
		$student_grades = [];
		/*
		foreach($sections_teaching as $sect){
			
			$takes_role = $em->getRepository('AppBundle\Entity\Role')->findOneBy(array('role_name' => 'Takes'));
			
			$qb_usr = $em->createQueryBuilder();
			$qb_usr->select('usr')
				->from('AppBundle\Entity\UserSectionRole', 'usr')
				->where('usr.user = ?1')
				->andWhere('usr.section = ?2')
				->andWhere('usr.role = ?3')
				->setParameter(1, $user)
				->setParameter(2, $sect)
				->setParameter(3, $takes_role);

			$usr_query = $qb_usr->getQuery();
			$section_takers = $usr_query->getResult();
			
			$tot_grd = 0;
			foreach($section_takers as $taker){
				$grd = $grader->getSectionGrade($user, $sect);
				$grd = $grd['percentage_adj'];
				
				$tot_grd += $grd;
			}
			
			if(count($section_takers) > 0){
				$tot_grd = $tot_grd/count($section_takers);
			}
			
			$student_grades[$sect->id] = $tot_grd;
		}
		*/
		
		$grades = $grader->getAllSectionGrades($user);
		
		return $this->render('home/index.html.twig', [
			'user' => $user,
			
			'usersectionroles' => $usersectionroles,
			'assignments' => $assignments,
			'sections_taking' => $sections_taking,
			'sections_teaching' => $sections_teaching,
			
			'grades' => $grades,
			'user_impersonators' => $users,
			
			'student_grades' => $student_grades,
		]);

    }
}

?>