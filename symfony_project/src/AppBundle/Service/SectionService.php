<?php

namespace AppBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;

use \DateTime;
use \DateInterval;

class SectionService
{
    private $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
	}
	
    public function getNonDeletedSectionsForHome($entityManager) {
		$builder = $entityManager->createQueryBuilder();
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
	
	public function getSectionById($entityManager, $sectionId) {
		return $entityManager->find("AppBundle\Entity\Section", $sectionId);
	}

	public function insertSection($entityManager, $section, $shouldFlush = true) {
		$entityManager->persist($section);
		if ($shouldFlush) {
			$entityManager->flush();
		}
	}
}
?>