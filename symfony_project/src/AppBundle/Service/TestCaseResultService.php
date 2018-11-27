<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManagerInterface;

use AppBundle\Entity\TestcaseResult;

class TestCaseResultService {

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
	}

	public function deleteTestCaseResultsByTestCase($testcase, $shouldFlush = true){
		$builder = $this->entityManager->createQueryBuilder();
		$builder->select("t")
			    ->from("AppBundle\Entity\TestcaseResult", "t")
			    ->where("t.testcase = (?1)")
			    ->setParameter(1, $testcase);
        $testCaseResults = $builder->getQuery();
        $testCaseResults = $testCaseResults->getResult();
        
		foreach($testCaseResults as $testCaseResult){
            $this->deleteTestCaseResult($testCaseResult, $shouldFlush);
        }
    }
    
    public function deleteTestCaseResult($testCaseResult, $shouldFlush = true){
		$this->entityManager->remove($testCaseResult);
		if ($shouldFlush) {
			$this->entityManager->flush();
		}
	}

	public function insertTestCaseResult($TestCaseResult, $shouldFlush = true) {
		$this->entityManager->persist($TestCaseResult);
		if ($shouldFlush) {
			$this->entityManager->flush();
		}
	}
}
?>