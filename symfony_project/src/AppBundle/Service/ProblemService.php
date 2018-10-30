<?php

namespace AppBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;

use \DateTime;
use \DateInterval;

class ProblemService
{
    private $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
	}

	public function deleteProblem($entityManager, $problem) {
		$entityManager->remove($problem);
		$entityManager->flush();
	}
	
	public function getProblemById($entityManager, $problemId) {
		return $entityManager->find("AppBundle\Entity\Problem", $problemId);
	}

	public function getProblemsByAssignment($entityManager, $assignment) {
		$builder = $entityManager->createQueryBuilder();
		$builder->select("p")
			->from("AppBundle\Entity\Problem", "p")
			->where("p.assignment = (?1)")
			->setParameter(1, $assignment);

		$problemQuery = $builder->getQuery();
		return $problemQuery->getResult();
	}

	public function getProblemsByObject($entityManager, $findBy) {
		return $entityManager->getRepository("AppBundle\Entity\Problem")->findBy($findBy);
	}

	public function insertProblem($entityManager, $problem) {
		$entityManager->persist($problem);
		$entityManager->flush();
	}
}
?>