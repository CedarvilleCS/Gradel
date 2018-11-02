<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManagerInterface;

use AppBundle\Entity\Submission;

use \DateTime;
use \DateInterval;

class SubmissionService {
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
	}

	public function createSubmissionFromTrialAndTeamForCompilationSubmit($trial, $team) {
		$submission = new Submission($trial, $team);
		return $submission;
	}

	public function createSubmissionFromProblemTeamAndUser($problem, $team, $user) {
		$submission = new Submission($problem, null, null);
		return $submission;
	}

	public function deleteAllSubmissionsForAssignmentClearSubmissions($problems) {
		$builder = $this->entityManager->createQueryBuilder();
		$builder->delete("AppBundle\Entity\Submission", "s")
		        ->where("s.problem IN (?1)")
		        ->setParameter(1, $problems);

		$deleteQuery = $builder->getQuery();
		$result = $deleteQuery->getResult();
		$this->entityManager->flush();
		return $result;
	}

	public function deleteSubmission($submission) {
		$this->entityManager->remove($submission);
		$this->entityManager->flush();
	}
	
	public function getBestSubmissionForAssignment($teamOrUser, $whereClause, $problem) {
		# get the best submission so far
		$builder = $this->entityManager->createQueryBuilder();
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

	public function getPreviousAcceptedSolutionForCompilationSubmit($teamOrUser, $whereClause, $problem) {
		$builder = $this->entityManager->createQueryBuilder();
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

	public function getAllSubmissionsForAssignment($teamOrUser, $whereClause, $problem) {
		$builder = $this->entityManager->createQueryBuilder();
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

	public function getSubmissionById($submissionId) {
		return $this->entityManager->find("AppBundle\Entity\Submission", $submissionId);
	}

	public function insertSubmission($submission, $shouldFlush = true) {
		$this->entityManager->persist($submission);
		if ($shouldFlush) {
			$this->entityManager->flush();
		}
	}
}
?>