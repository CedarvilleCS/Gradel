<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManagerInterface;

use AppBundle\Entity\Section;

use \DateTime;
use \DateInterval;

class SectionService {
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
	}

	public function createEmptySection() {
		return new Section();
	}
	
    public function getNonDeletedSectionsForHome() {
		$builder = $this->entityManager->createQueryBuilder();
		$builder->select("s")
		->from("AppBundle\Entity\Section", "s")
		->where("s.is_deleted = false")
		->andWhere("s.start_time < ?1")
		->andWhere("s.end_time > ?2")
		->setParameter(1, (new DateTime("now"))->add(new DateInterval("P30D")))
		->setParameter(2, (new DateTime("now"))->sub(new DateInterval("P14D")));
		
		$sectionQuery = $builder->getQuery();
		return $sectionQuery->getResult();
	}
	
	public function getSectionById($sectionId) {
		return $this->entityManager->find("AppBundle\Entity\Section", $sectionId);
	}

	public function insertSection($section, $shouldFlush = true) {
		$this->entityManager->persist($section);
		if ($shouldFlush) {
			$this->entityManager->flush();
		}
	}
}
?>