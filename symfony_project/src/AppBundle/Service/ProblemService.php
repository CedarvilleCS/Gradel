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
	
	public function getProblemById($entityManager, $problemId) {
		return $entityManager->find("AppBundle\Entity\Problem", $problemId);
	}
}
?>