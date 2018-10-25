<?php

namespace AppBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;

use AppBundle\Entity\Submission;

use \DateTime;
use \DateInterval;

class SubmissionService
{
    private $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
	}

	public function createSubmissionFromTrialAndTeamForCompilationSubmit($entityManager, $trial, $team) {
		$submission = new Submission($trial, $team);

		$entityManager->persist($submission);
		$entityManager->flush();

		return $submission;
	}

	public function deleteAllSubmissionsForAssignmentClearSubmissions($entityManager, $problems, $shouldFlush = false) {
		$builder = $entityManager->createQueryBuilder();
		$builder->delete("AppBundle\Entity\Submission", "s")
		        ->where("s.problem IN (?1)")
		        ->setParameter(1, $problems);

		$deleteQuery = $builder->getQuery();
		$result = $deleteQuery->getResult();
		if ($shouldFlush) {
			$entityManager->flush();
		}
		return $result;
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

	public function getPreviousAcceptedSolutionForCompilationSubmit($entityManager, $teamOrUser, $whereClause, $problem) {
		$builder = $entityManager->createQueryBuilder();
		$builder->select("s")
			->from("AppBundle\Entity\Submission", "s")
			->where("s.problem = ?1")
			->andWhere($whereClause)
			->andWhere("s.best_submission = true")
			->setParameter(1, $problem)
			->setParameter(2, $teamOrUser)
			->orderBy("s.timestamp", "DESC");
				
		$previousAcceptedQuery = $builder->getQuery();
		return $previousAcceptedQuery->getResult()[0];
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

	public function insertSubmission($entityManager, $submission, $shouldFlush = false) {
		$entityManager->persist($submission);
		if ($shouldFlush) {
			$entityManager->flush();
		}
	}
}
?>