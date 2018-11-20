<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManagerInterface;

class SemesterService {
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager){
        $this->entityManager = $entityManager;
    }

    public function getCurrentSemester() {
        $builder = $this->entityManager->createQueryBuilder();
        $builder->select("s")
                ->from("AppBundle\Entity\Semester", "s")
                ->where("s.is_current_semester = 1");
        $semester = $builder->getQuery()->getResult()[0];
        return $semester;
    }

    public function getSemesterBySeasonAndYear($term, $year) {
        $builder = $this->entityManager->createQueryBuilder();
        $builder->select("s")
                ->from("AppBundle\Entity\Semester", "s")
                ->where("s.is_current_semester = 1")
                ->setMaxResults(1);
        $semester = $builder->getQuery()->getResult()[0];
        return $semester;
    }

    public function insertSemester($semester, $shouldFlush = true){
        $this->entityManager->persist($semester);
		if ($shouldFlush) {
			$this->entityManager->flush();
		}
    }

    public function createSemesterByTermAndYear($term, $year, $isCurrentSemester){
        $semester = new Semester($year, $term, $isCurrentSemester);
        return $semester;
    }
}

?>