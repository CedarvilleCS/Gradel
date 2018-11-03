<?php

namespace AppBundle\Service;

use AppBundle\Entity\Assignment;

use Doctrine\ORM\EntityManagerInterface;

use \DateTime;
use \DateInterval;

class AssignmentService {
	private $entityManager;

	public function __construct(EntityManagerInterface $entityManager) {
		$this->entityManager = $entityManager;
	}

	public function createEmptyAssignment() {
		$assignment = new Assignment();
		return $assignment;
	}

	public function deleteAssignment($assignment, $shouldFlush = true) {
		$this->entityManager->remove($assignment);
		if ($shouldFlush) {
			$this->entityManager->flush();
		}
	}
	
	public function getAssignmentById($assignmentId) {
		return $this->entityManager->find("AppBundle\Entity\Assignment", $assignmentId);
	}
	
    public function getAssignmentsSortedByDueDate($sections) {
		$twoWeeksDate = new DateTime();
		$twoWeeksDate = $twoWeeksDate->add(new DateInterval("P2W"));
		
		$builder = $this->entityManager->createQueryBuilder();
		$builder->select("a")
			->from("AppBundle\Entity\Assignment", "a")
			->where("a.section IN (?1)")
			->andWhere("a.end_time > (?2)")
			->andWhere("a.end_time < (?3)")
			->setParameter(1, $sections)
			->setParameter(2, new DateTime())
			->setParameter(3, $twoWeeksDate)
			->orderBy("a.end_time", "ASC");
		
		$assignmentQuery = $builder->getQuery();		
		return $assignmentQuery->getResult();
	}

	public function getAssignmentsSortedByDueDateForSection($section) {
		$twoWeeksDate = new DateTime();
		$twoWeeksDate = $twoWeeksDate->add(new DateInterval("P2W"));
		
		$builder = $this->entityManager->createQueryBuilder();
		$builder->select("a")
			->from("AppBundle\Entity\Assignment", "a")
			->where("a.section = (?1)")
			->andWhere("a.end_time > (?2)")
			->andWhere("a.end_time < (?3)")
			->setParameter(1, $section)
			->setParameter(2, new DateTime())
			->setParameter(3, $twoWeeksDate)
			->orderBy("a.end_time", "ASC");
		
		$assignmentQuery = $builder->getQuery();		
		return $assignmentQuery->getResult();
	}
	
	public function getAssignmentsBySection($section) {
		$builder = $this->entityManager->createQueryBuilder();
		$builder->select("a")
			->from("AppBundle\Entity\Assignment", "a")
			->where("a.section = (?1)")
			->orderBy("a.start_time", "ASC")
			->setParameter(1, $section);
		
		$assignmentQuery = $builder->getQuery();
		return $assignmentQuery->getResult();
	}

	public function insertAssignment($assignment, $shouldFlush = true) {
		$this->entityManager->persist($assignment);
		if ($shouldFlush) {
			$this->entityManager->flush();
		}
	}
}
?>
