<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManagerInterface;

use AppBundle\Entity\Testcase;

use \DateTime;
use \DateInterval;

class TestCaseService {

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
	}

	public function selectTestCasesByProblem($problem){
		$builder = $this->entityManager->createQueryBuilder()
				->select("t")
				->from("AppBundle\Entity\Testcase", "t")
				->where("t.problem = (?1)")
				->setParameter(1, $problem);
		return $testcases = $builder->getQuery()->getResult();
	}

	public function deleteTestCase($testcase, $shouldFlush = true){
		$this->entityManager->remove($testcase);
		if ($shouldFlush) {
			$this->entityManager->flush();
		}
	}

	public function insertTestCase($testCase, $shouldFlush = true) {
		$this->entityManager->persist($testCase);
		if ($shouldFlush) {
			$this->entityManager->flush();
		}
	}
}
?>