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

    public function getSemesterById($semesterId) {
        return $this->entityManager->find("AppBundle\Entity\Semester", $semesterId);
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

    public function createSemesterByTermAndYear($term, $year, $isCurrentSemester = false){
        $semester = new Semester($term, $year, $isCurrentSemester);
        return $semester;
    }

    public function updateCurrentSemesterByTermAndYear($term, $year){
        $semester = $this->getCurrentSemester();
        $semester->is_current_semester = 0;

        $semester = $this->getSemesterByTermAndYear($term, $year);
        $semester->is_current_semester = 1;
        $this->entityManager->flush();
    }
}

?>