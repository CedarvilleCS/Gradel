<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Entity\Course;
use AppBundle\Entity\UserSectionRole;
use AppBundle\Entity\Section;
use AppBundle\Entity\Assignment;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Psr\Log\LoggerInterface;


class ProfessorsectionController extends Controller
{
    public function professorsectionAction($userId, $sectionId) {

      // $em = $this->getDoctrine()->getManager();

      // $builder = $em->createQueryBuilder();
      // $builder->select('section')
      //         ->from('AppBundle\Entity\Section section')
      //         ->where('section.id = :id')
      //         ->setParameter("id", $sectionId);
      // $query = $builder->getQuery();
      // $section = $query->getSingleResult();

      return $this->render('professor/section/index.html.twig', [
        'section' => $section,
        'userId' => $userId,
        'sectionId' => $sectionId
      ]);
    }
}
