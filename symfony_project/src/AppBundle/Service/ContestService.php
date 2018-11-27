<?php

namespace AppBundle\Service;

use AppBundle\Entity\Assignment;

use Doctrine\ORM\EntityManagerInterface;

use \DateTime;
use \DateInterval;

class ContestService {
	private $entityManager;

	public function __construct(EntityManagerInterface $entityManager) {
		$this->entityManager = $entityManager;
	}

	public function createEmptyContest() {
		return new Assignment();
	}

	public function deleteContest($contest, $shouldFlush = true) {
		$this->entityManager->remove($contest);
		if ($shouldFlush) {
			$this->entityManager->flush();
		}
	}
	
	public function getContestById($contestId) {
		return $this->entityManager->find("AppBundle\Entity\Assignment", $contestId);
	}

	public function insertContest($contest, $shouldFlush = true) {
		$this->entityManager->persist($contest);
		if ($shouldFlush) {
			$this->entityManager->flush();
		}
	}
}
?>
