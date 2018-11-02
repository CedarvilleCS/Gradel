<?php

namespace AppBundle\Service;

use AppBundle\Entity\Course;

use Doctrine\ORM\EntityManagerInterface;

class CourseService {
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
    }

    public function createEmptyCourse() {
        return new Course();
    }

    public function getAll() {
        return $this->entityManager->getRepository("AppBundle\Entity\Course")->findAll();
    }

    public function getCourseById($courseId) {
        return $this->entityManager->find('AppBundle\Entity\Course', $courseId);
    }

    public function getNonDeletedCourses() {
        $builder = $this->entityManager->createQueryBuilder();
		$builder->select("c")
				->from("AppBundle\Entity\Course", "c")
				->where("c.is_deleted = false");
        $query = $builder->getQuery();
        
		return $query->getResult();	
    }

    public function insertCourse($course, $shouldFlush = true) {
        $this->entityManager->persist($course);
        if ($shouldFlush) {
            $this->entityManager->flush();
        }
    }
}
?>
