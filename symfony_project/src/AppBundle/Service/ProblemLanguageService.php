<?php

namespace AppBundle\Service;

use AppBundle\Entity\ProblemLanguage;

use Doctrine\ORM\EntityManagerInterface;

class ProblemLanguageService {
	private $entityManager;

	public function __construct(EntityManagerInterface $entityManager) {
		$this->entityManager = $entityManager;
	}
	
	public function createProblemLanguage($problem, $language) {
		$problemLanguage = new ProblemLanguage();

		$problemLanguage->problem = $problem;
		$problemLanguage->language = $language;

		return $problemLanguage;
	}

	public function deleteProblemLanguage($problemLanguage, $shouldFlush = true) {
		$this->entityManager->remove($problemLanguage);
		if ($shouldFlush) {
			$this->entityManager->flush();
		}
	}

    public function getProblemLanguagesByProblem($problem) {
		$builder = $this->entityManager->createQueryBuilder();
		$builder->select("pl")
			->from("AppBundle\Entity\ProblemLanguage", "pl")
			->where("pl.problem = (?1)")
			->setParameter(1, $problem);

		$problemLanguageQuery = $builder->getQuery();
		return $problemLanguageQuery->getResult();
	}

	public function insertProblemLanguage($problemLanguage, $shouldFlush = true) {
		$this->entityManager->persist($problemLanguage);
		if ($shouldFlush) {
			$this->entityManager->flush();
		}
	}
}
?>