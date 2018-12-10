<?php
namespace AppBundle\Service;

use AppBundle\Entity\Problem;

use Doctrine\ORM\EntityManagerInterface;

use AppBundle\Service\FeedbackService;
use AppBundle\Service\ProblemLanguageService;
use AppBundle\Service\QueryService;
use AppBundle\Service\SubmissionService;
use AppBundle\Service\TestCaseResultService;
use AppBundle\Service\TestCaseService;
use AppBundle\Service\TrialService;

use \DateTime;
use \DateInterval;

class ProblemService {
    private $entityManager;
    private $feedbackService;
    private $problemLanguageService;
    private $queryService;
    private $submissionService;
    private $testCaseResultService;
    private $testCaseService;

    public function __construct(EntityManagerInterface $entityManager,
                                FeedbackService $feedbackService,
                                ProblemLanguageService $problemLanguageService,
                                QueryService $queryService,
                                SubmissionService $submissionService,
                                TestCaseResultService $testCaseResultService,
                                TestCaseService $testCaseService,
                                TrialService $trialService) {
        $this->entityManager = $entityManager;
        $this->feedbackService = $feedbackService;
        $this->problemLanguageService = $problemLanguageService;
        $this->queryService = $queryService;
        $this->submissionService = $submissionService;
        $this->testCaseResultService = $testCaseResultService;
        $this->testCaseService = $testCaseService;
        $this->trialService = $trialService;
    }
    
	public function createEmptyProblem() {
		return new Problem();
    }
    
	public function deleteProblem($problem, $shouldFlush = true) {
        //delete all dependent objects, then delete problem
        $this->queryService->deleteQueriesByProblem($problem, false);
        $this->submissionService->deleteSubmissionsByProblem($problem, false);
        $this->trialService->deleteTrialsByProblem($problem, false);
        
        $testcases = $this->testCaseService->selectTestCasesByProblem($problem);
        foreach ($testcases as $testcase){
            $this->testCaseResultService->deleteTestCaseResultsByTestCase($testcase, false);
            $this->feedbackService->deleteFeedbacksByTestcase($testcase, false);
            $this->testCaseService->deleteTestCase($testcase, false);
        }

        $this->entityManager->remove($problem);
		if ($shouldFlush) {
			$this->entityManager->flush();
		}
	}
	
	public function getProblemById($problemId) {
		return $this->entityManager->find("AppBundle\Entity\Problem", $problemId);
	}
	public function getProblemsByAssignment($assignment) {
		$builder = $this->entityManager->createQueryBuilder();
		$builder->select("p")
			->from("AppBundle\Entity\Problem", "p")
			->where("p.assignment = (?1)")
			->setParameter(1, $assignment);
		$problemQuery = $builder->getQuery();
		return $problemQuery->getResult();
	}
	public function getProblemsByObject($findBy) {
		return $this->entityManager->getRepository("AppBundle\Entity\Problem")->findBy($findBy);
	}
	public function insertProblem($problem, $shouldFlush = true) {
		$this->entityManager->persist($problem);
		if ($shouldFlush) {
			$this->entityManager->flush();
		}
	}
}
?>