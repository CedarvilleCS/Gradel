<?php

namespace AppBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;

class ProblemLanguageService
{
    private $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
	}
	
	public function createProblemLanguage($problem, $language) {
		$problemLanguage = new ProblemLanguage();

		$problemLanguage->language = $language;
		$problemLanguage->problem = $problem;

		return $problemLanguage;
	}

    public function getProblemLanguagesByProblem($entityManager, $problem) {
		$builder = $entityManager->createQueryBuilder();
		$builder->select("pl")
			->from("AppBundle\Entity\ProblemLanguage", "pl")
			->where("pl.problem = (?1)")
			->setParameter(1, $problem);

		$problemLanguageQuery = $builder->getQuery();
		return $problemLanguageQuery->getResult();
	}

	public function insertProblemLanguage($entityManager, $problemLanguage) {
		$entityManager->persist($problemLanguage);
		$entityManager->flush();
	}
}
?>