<?php

namespace AppBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;

use \DateTime;
use \DateInterval;

class SubmissionService
{
    private $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
	}
	
	public function getBestSubmissionForAssignment($entityManager, $teamOrUser, $whereClause, $problem) {
		# get the best submission so far
		$builder = $entityManager->createQueryBuilder();
		$builder->select("s")
			->from("AppBundle\Entity\Submission", "s")
			->where($whereClause)
			->andWhere("s.problem = ?2")
			->andWhere("s.best_submission = true")
			->setParameter(1, $teamOrUser)
			->setParameter(2, $problem);
			
		$bestSubmissionQuery = $builder->getQuery();
		return $bestSubmissionQuery->getOneOrNullResult();
	}

	public function getAllSubmissionsForAssignment($entityManager, $teamOrUser, $whereClause, $problem) {
		$builder = $entityManager->createQueryBuilder();
		$builder->select("s")
			->from("AppBundle\Entity\Submission", "s")
			->where($whereClause)
			->andWhere("s.problem = ?2")
			->orderBy("s.id", "DESC")
			->setParameter(1, $teamOrUser)
			->setParameter(2, $problem);
		$allSubmissionsQuery = $builder->getQuery();
		return $allSubmissionsQuery->getResult();
	}

	public function getSubmissionById($entityManager, $submissionId) {
		return $entityManager->find("AppBundle\Entity\Submission", $submissionId);
	}
}
?>