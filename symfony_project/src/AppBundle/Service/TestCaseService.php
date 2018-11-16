<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManagerInterface;

use AppBundle\Entity\Testcase;

use AppBundle\Service\SubmissionService;

use \DateTime;
use \DateInterval;

use Psr\Log\LoggerInterface;

class TestCaseService {
	private $entityManager;
	private $logger;
	private $submissionService;

	public function __construct(EntityManagerInterface $entityManager,
	                            LoggerInterface $logger,
	                            SubmissionService $submissionService) {
		$this->entityManager = $entityManager;
		$this->logger = $logger;
		$this->submissionService = $submissionService;
	}

	public function insertTestCase($testCase, $shouldFlush = true) {
		$this->entityManager->persist($testCase);
		if ($shouldFlush) {
			$this->entityManager->flush();
		}
	}

	public function getTestCaseInfoFromTeamOrUserAndProblem($teamOrUser, $whereClause, $problem) {
		$testCases = $problem->testcases;
		$bestSubmission = $this->submissionService->getBestSubmissionForAssignment($teamOrUser, $whereClause, $problem);

		$numberOfCorrectTestCaseResults = 0;
		if ($bestSubmission) {
			$builder = $this->entityManager->createQueryBuilder();
			$builder->select("tcr")
				->from("AppBundle\Entity\TestcaseResult", "tcr")
				->where("tcr.submission = (?1)")
				->andWhere("tcr.is_correct = 1")
				->setParameter(1, $bestSubmission);
			$testCaseResultQuery = $builder->getQuery();
			$numberOfCorrectTestCaseResults = count($testCaseResultQuery->getResult());
		}

		return [
			'numberOfTestCases' => count($problem->testcases),
			'numberOfCorrectTestCases' => $numberOfCorrectTestCaseResults
		];
	}

	private function logError($message) {
        $errorMessage = "TestCaseService: ".$message;
        $this->logger->error($errorMessage);
        return $errorMessage;
    }
}
?>