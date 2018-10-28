<?php

namespace AppBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;

use AppBundle\Entity\Course;

class CourseService 
{
    private $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function createEmptyCourse() {
        return new Course();
    }

    public function getCourseById($entityManager, $courseId) {
        return $entityManager->find('AppBundle\Entity\Course', $courseId);
    }

    public function getAll($entityManager) {
        return $entityManager->getRepository("AppBundle\Entity\Course")->findAll();
    }

    public function insertCourse($entityManager, $course) {
        $entityManager->persist($course);
        $entityManager->flush();
    }
}
?>
