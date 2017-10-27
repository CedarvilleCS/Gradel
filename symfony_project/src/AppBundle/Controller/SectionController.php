<?php

namespace AppBundle\Controller;

use \DateTime;

use AppBundle\Entity\User;
use AppBundle\Entity\Course;
use AppBundle\Entity\UserSectionRole;
use AppBundle\Entity\Section;
use AppBundle\Entity\Assignment;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


use Psr\Log\LoggerInterface;


class SectionController extends Controller
{
    public function sectionAction($userId, $sectionId) {

      $em = $this->getDoctrine()->getManager();

      $builder = $em->createQueryBuilder();
      $builder->select('section')
              ->from('AppBundle\Entity\Section section')
              ->where('section.id = :id')
              ->setParameter("id", $sectionId);
      $query = $builder->getQuery();
      $section = $query->getSingleResult();

			$qb = $em->createQueryBuilder();
			$qb->select('u')
					->from('AppBundle\Entity\User', 'u')
					->where('u.id = ?1')
					->setParameter(1, $userId);

			$query = $qb->getQuery();
			$user = $query->getSingleResult();

			$qb->select('usr')
					->from('AppBundle\Entity\UserSectionRole', 'usr')
					->where('usr.user = ?1')
					->andWhere('usr.section = ?2')
					->setParameter(1, $user->id)
					->setParameter(2, $sectionId);


			$query = $qb->getQuery();
			$usr = $query->getSingleResult();

      return $this->render('default/section/index.html.twig', [
        'section' => $section,
        'userId' => $userId,
        'sectionId' => $sectionId,
				'usr' => $usr,
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

      // $form->handleRequest($request);

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

    public function insertSectionAction(Request $request, $userId, $courseId, $name, $semester, $year, $start_time, $end_time, $is_public, $is_deleted) {

      $em = $this->getDoctrine()->getManager();

      $course = $em->getReference('AppBundle\Entity\Course', $courseId);
      echo json_encode($course->name);

      $user = $em->getReference('AppBundle\Entity\User', $userId);
      echo "<br/>";
      echo json_encode($user);



      $section = new Section();
      $section->name = $name;
      $section->course = $course;
      $section->owner = $user;
      $section->semester = $semester;
      $section->year = $year;
      $section->start_time =  new DateTime("now");
      $section->end_time = new DateTime("now");
      $section->is_deleted = $is_deleted;
      $section->is_public = $is_public;

      // TODO: user section role insert

      $builder = $em->createQueryBuilder();

      $em->persist($section);
      $em->flush();

      echo $section->id . " is the hting";

      // $builder->insert('section')
      //         ->values(
      //             array(
      //               'name' => 'blah',
      //               'course_id' => 1,
      //               'owner_id' => 1,
      //               'semester' => 'Fall',
      //               'year' => 2017,
      //               'start_time' => '2017-08-21',
      //               'end_time' => '2017-12-16',
      //               'is_deleted'=> false,
      //               'is_public' => false
      //             )
      //           );

      return new Response("it worked");
    }
}
