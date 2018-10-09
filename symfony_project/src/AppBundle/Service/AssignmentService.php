<?php

namespace AppBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;

use \DateTime;
use \DateInterval;

class AssignmentService 
{
    private $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
	}

	public function createEmptyAssignment($entityManager) {
		$assignment = new Assignment();
		$entityManager->persist($assignment);
		$entityManager->flush();
		return $assignment;
	}

	public function insertTeam($entityManager, $assignment) {
		$entityManager->persist($assignment);
		$entityManager->flush();
	}

	public function deleteAssignment($entityManager, $assignment) {
		$entityManager->remove($assignment);
		$entityManager->flush();
	}
	
	public function getAssignmentById($entityManager, $assignmentId) {
		return $entityManager->find("AppBundle\Entity\Assignment", $assignmentId);
	}

    public function getAssignmentsSortedByDueDateForHome($entityManager, $sections) {
        $twoWeeksDate = new DateTime();
		$twoWeeksDate = $twoWeeksDate->add(new DateInterval("P2W"));
		
		$builder = $entityManager->createQueryBuilder();
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
}
?>