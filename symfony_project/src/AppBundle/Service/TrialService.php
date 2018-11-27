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
	
	public function createTrial($user, $problem, $showDescription = "false") {
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

	public function deleteTrialsByProblem($problem, $shouldFlush = true){
		$builder = $this->entityManager->createQueryBuilder()
			->select("t")
			->from("AppBundle\Entity\Trial", "t")
			->where("t.problem = (?1)")
			->setParameter(1, $problem);
		$trials = $builder->getQuery()->getResult();

		foreach ($trials as $trial){
			$this->deleteTrial($trial, $shouldFlush);
		}
	}

	public function deleteTrial($trial, $shouldFlush = true) {
		$this->entityManager->remove($trial);
		if ($shouldFlush) {
			$this->entityManager->flush();
		}
	}
	public function insertTrial($trial, $shouldFlush = true) {
		$this->entityManager->persist($trial);
		if ($shouldFlush) {
			$this->entityManager->flush();
		}
	}
}
?>
