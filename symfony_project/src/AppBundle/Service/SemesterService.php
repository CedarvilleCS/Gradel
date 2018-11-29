<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManagerInterface;

use AppBundle\Entity\Semester;

class SemesterService {
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager){
        $this->entityManager = $entityManager;
    }

    public function getAllSemesters() {
        return $this->entityManager->getRepository("AppBundle\Entity\Semester")->findAll();
    }

    public function getCurrentSemester() {
        $builder = $this->entityManager->createQueryBuilder();
        $builder->select("s")
                ->from("AppBundle\Entity\Semester", "s")
                ->where("s.is_current_semester = (?1)")
                ->setParameter(1, 1);
        $semester = $builder->getQuery()->getResult();
        return $semester[0];
    }

    public function getSemesterByTermAndYear($term, $year) {
        $builder = $this->entityManager->createQueryBuilder();
        $builder->select("s")
                ->from("AppBundle\Entity\Semester", "s")
                ->where("s.term = (?1)")
                ->andWhere("s.year = (?2)")
                ->setParameter(1, $term)
                ->setParameter(2, $year);
        $semester = $builder->getQuery()->getResult();
        return $semester[0];
    }

    public function insertSemester($semester, $shouldFlush = true){
        $this->entityManager->persist($semester);
		if ($shouldFlush) {
			$this->entityManager->flush();
		}
    }

    public function createSemesterByTermAndYear($term, $year, $isCurrentSemester){
        $semester = new Semester($term, $year, $isCurrentSemester);
        return $semester;
    }
}

?>