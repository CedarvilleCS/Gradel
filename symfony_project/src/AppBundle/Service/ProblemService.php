<?php

namespace AppBundle\Service;

use AppBundle\Entity\Problem;

use Doctrine\ORM\EntityManagerInterface;

use \DateTime;
use \DateInterval;

class ProblemService {
	private $entityManager;

	public function __construct(EntityManagerInterface $entityManager) {
		$this->entityManager = $entityManager;
	}

	public function createEmptyProblem() {
		return new Problem();
	}

	public function deleteProblem($problem, $shouldFlush = true) {
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