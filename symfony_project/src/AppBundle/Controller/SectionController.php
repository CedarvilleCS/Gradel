<?php

namespace AppBundle\Controller;

use \DateTime;

use AppBundle\Entity\User;
use AppBundle\Entity\Course;
use AppBundle\Entity\UserSectionRole;
use AppBundle\Entity\Role;
use AppBundle\Entity\Section;
use AppBundle\Entity\Assignment;
use AppBundle\Entity\Submission;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


use Psr\Log\LoggerInterface;


class SectionController extends Controller
{
    public function sectionAction($userId, $sectionId) {

		$em = $this->getDoctrine()->getManager();
		  
		$user = $this->get('security.token_storage')->getToken()->getUser();

		if(!get_class($user)){		
			die("USER DOES NOT EXIST");
		}

		$section_entity = $em->find('AppBundle\Entity\Section', $sectionId);
		
		if(!$section_entity){
			die("SECTION DOES NOT EXIST!");
		}
		
		# GET ALL ASSIGNMENTS
		$qb = $em->createQueryBuilder();
		$qb->select('a')
			->from('AppBundle\Entity\Assignment', 'a')
			->where('a.section = ?1')
			->orderBy('a.end_time', 'ASC')
			->setParameter(1, $section_entity);
		
		$query = $qb->getQuery();
		$assignments = $query->getResult();
		
		# GET FUTURE ASSIGNMENTS
		$qb_asgn = $em->createQueryBuilder();
		$qb_asgn->select('a')
				->from('AppBundle\Entity\Assignment', 'a')
				->where('a.section = ?1')
				->andWhere('a.end_time > ?2')
				->setParameter(1, $section_entity)
				->setParameter(2, new DateTime())
				->orderBy('a.end_time', 'ASC');
				
		$asgn_query = $qb_asgn->getQuery();	
		$future_assig = $asgn_query->getResult();
		
		# GET ALL USERS
		$qb_user = $em->createQueryBuilder();
		$qb_user->select('usr')
			->from('AppBundle\Entity\UserSectionRole', 'usr')
			->where('usr.section = ?1')
			->setParameter(1, $section_entity);
		
		$user_query = $qb_user->getQuery();
		$usersectionroles = $user_query->getResult();		
		
		$section_takers = [];
		$section_teachers = [];
		$section_helpers = [];
		
		foreach($usersectionroles as $usr){
			if($usr->role->role_name == "Takes"){
				$section_takers[] = $usr->user;
			} else if($usr->role->role_name == "Teaches"){
				$section_teachers[] = $usr->user;
			} else if($usr->role->role_name == "Helps"){
				$section_helpers[] = $usr->user;
			}
		}
		
		# GET ALL STUDENT SUBMISSIONS IN THE CLASS
		$student_subs = [];
		
		foreach($assignments as $asgn){
						
			$qb_subs = $em->createQueryBuilder();
			$qb_subs->select('s')
					->from('AppBundle\Entity\Submission', 's')
					->where('s.problem IN (?1)')
					->andWhere('s.is_accepted = true')
					->setParameter(1, $asgn->problems);
					
			$subs_query = $qb_subs->getQuery();	
			$subs = $subs_query->getResult();			
			
			# switch this to use teams
			foreach($subs as $sub){				
				foreach($sub->team->users as $user){
					
					if($sub->percentage == 1){
						$student_subs[$asgn->id][$user->id] = "GOOD";
					} else {
						$student_subs[$asgn->id][$user->id] = "BAD";
					}
				}
			}
		}
		
		# get all of the problems to get all of the submissions
		$allprobs = [];		
		foreach($section_entity->assignments as $asgn){
			foreach($asgn->problems as $prob){
				$allprobs[] = $prob;
			}
		}
		$qb_submissions = $em->createQueryBuilder();
		$qb_submissions->select('s')
				->from('AppBundle\Entity\Submission', 's')
				->where('s.problem IN (?1)')
				->orderBy('s.timestamp', 'DESC')
				->setParameter(1, $allprobs);
				
		$submission_query = $qb_submissions->getQuery();	
		$submissions = $submission_query->getResult();
		
				
		return $this->render('default/section/index.html.twig', [
			'section' => $section,
			'sectionId' => $sectionId,
			'userId' => $userId,
			'assignments' => $assignments,
			'future_assigs' => $future_assig,
			'student_subs' => $student_subs,
			'recent_submissions' => $submissions,
			'section_takers' => $section_takers,
			'section_teachers' => $section_teachers,
			'section_helpers' => $section_helpers,
		]);
    }

	public function newSectionAction($userId) {

      $em = $this->getDoctrine()->getManager();
      $builder = $em->createQueryBuilder();

      $builder->select('c')
              ->from('AppBundle\Entity\Course', 'c')
              ->where('1 = 1');
      $query = $builder->getQuery();
      $sections = $query->getResult();


      $section = new Section();
      $form = $this->createFormBuilder($section)
                    ->add('name', TextType::class)
                    ->add('year', DateType::class)
                    ->add('save', SubmitType::class, array('label' => 'Create Section'))
                    ->getForm();

      $builder = $em->createQueryBuilder();
      $builder->select('u')
              ->from('AppBundle\Entity\User', 'u')
              ->where('1 = 1');
      $query = $builder->getQuery();
      $users = $query->getResult();

			return $this->render('default/section/new.html.twig', [
        'userId' => $userId,
        'sections' => $sections,
        'users' => $users,
        'form' => $form->createView(),
			]);
		}

    public function editSectionAction($userId, $sectionId) {
      $em = $this->getDoctrine()->getManager();
      $builder = $em->createQueryBuilder();

      $section = $em->find('AppBundle\Entity\Section', $sectionId);

      return $this->render('default/section/edit.html.twig', [
        'userId' => $userId,
        'section' => $section,
      ]);
    }

    public function insertSectionAction(Request $request, $userId, $courseId, $name, $students, $semester, $year, $start_time, $end_time, $is_public, $is_deleted) {

      echo "<br/>";

      $em = $this->getDoctrine()->getManager();
      $course = $em->find('AppBundle\Entity\Course', $courseId);
      $section = new Section();
      $section->name = $name;
      $section->course = $course;
      $section->semester = $semester;
      $section->year = $year;
      $section->start_time =  new DateTime("now");
      $section->end_time = new DateTime("now");
      $section->is_deleted = $is_deleted;
      $section->is_public = $is_public;

      $em->persist($section);
      $em->flush();

      // TODO: user section role insert for prof
      $role = $em->getRepository('AppBundle\Entity\Role')->findOneBy(array('role_name' => 'Teaches'));
      $teacher = $em->find('AppBundle\Entity\User', $userId);
      $usr = new UserSectionRole($teacher, $section, $role);
      $em->persist($usr);
      $em->flush();


      // insert students into the course
      $role = $em->getRepository('AppBundle\Entity\Role')->findOneBy(array('role_name' => 'Takes'));

      foreach (json_decode($students) as $student) {
        echo $student;
        echo "<br/>";

        if ($student != "") {
          $user = $em->getRepository('AppBundle\Entity\User')->findOneBy(array('email' => $student));

          echo $user->getFirstName();
          echo "<br/>";

          $usr = new UserSectionRole();
          $usr->user = $user;
          $usr->role = $role;
          $usr->section = $section;
          $em->persist($usr);
          $em->flush();
        }
      }
      echo $section->id;
      return new RedirectResponse($this->generateUrl('section_edit', array('userId' => $userId, 'sectionId' => $section->id)));
    }

    private function generateDateTime($year, $date) {
      // TODO: create string based on start and end times for the fields in doctrine entity
    }
}
