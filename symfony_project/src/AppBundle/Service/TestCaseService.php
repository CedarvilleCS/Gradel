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

	public function insertTestCase($testCase, $shouldFlush = true) {
		$this->entityManager->persist($testCase);
		if ($shouldFlush) {
			$this->entityManager->flush();
		}
	}
}
?>