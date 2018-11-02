<?php

namespace AppBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;

use AppBundle\Entity\Testcase;

use \DateTime;
use \DateInterval;

class TestCaseService
{
    private $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
	}

	public function insertTestCase($entityManager, $testCase, $shouldFlush = true) {
		$entityManager->persist($testCase);
		if ($shouldFlush) {
			$entityManager->flush();
		}
	}
}
?>