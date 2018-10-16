<?php

namespace AppBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;

class ProblemLanguageService
{
    private $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
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

	public function insertProblemLanguage($entityManager, $problemLanguage, $shouldFlush = false) {
		$entityManager->persist($problemLanguage);
		if ($shouldFlush) {
			$entityManager->flush();
		}
	}
}
?>