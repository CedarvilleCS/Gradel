<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManagerInterface;

use AppBundle\Entity\Trial;

use \DateTime;
use \DateInterval;

class TrialService {
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
	}

	public function createTrial($user, $problem, $showDescription) {
		$trial = new Trial();

		$trial->user = $user;
		$trial->problem = $problem;		
		$trial->show_description = $showDescription;

		return $trial;
	}
	
	public function getTrialForAssignment($user, $problem) {
		$builder = $this->entityManager->createQueryBuilder();
		$builder->select("t")
				->from("AppBundle\Entity\Trial", "t")
				->where("t.user = ?1")
				->andWhere("t.problem = ?2")
				->setParameter(1, $user)
				->setParameter(2, $problem);

		$trialQuery = $builder->getQuery();
		return $trialQuery->getOneorNullResult();
	}

	public function getTrialById($trialId) {
		return $this->entityManager->find("AppBundle\Entity\Trial", $trialId);
	}

	public function insertTrial($trial, $shouldFlush = true) {
		$this->entityManager->persist($trial);
		if ($shouldFlush) {
			$this->entityManager->flush();
		}
	}
}
?>