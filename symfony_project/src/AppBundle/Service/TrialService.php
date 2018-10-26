<?php

namespace AppBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;

use AppBundle\Entity\Trial;

use \DateTime;
use \DateInterval;

class TrialService
{
    private $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
	}

	public function createTrial($user, $problem, $showDescription) {
		$trial = new Trial();

		$trial->user = $user;
		$trial->problem = $problem;		
		$trial->show_description = $showDescription;

		return $trial;
	}
	
	public function getTrialForAssignment($entityManager, $user, $problem) {
		$builder = $entityManager->createQueryBuilder();
		$builder->select("t")
				->from("AppBundle\Entity\Trial", "t")
				->where("t.user = ?1")
				->andWhere("t.problem = ?2")
				->setParameter(1, $user)
				->setParameter(2, $problem);

		$trialQuery = $builder->getQuery();
		return $trialQuery->getOneorNullResult();
	}

	public function getTrialById($entityManager, $trialId) {
		return $entityManager->find("AppBundle\Entity\Trial", $trialId);
	}

	public function insertTrial($entityManager, $trial) {
		$entityManager->persist($trial);
		$entityManager->flush();
	}
}
?>