<?php

namespace AppBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;

class UserService
{
    private $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function getCurrentUser() {
        return $this->container->get("security.token_storage")->getToken()->getUser();
    }

    public function getUsersToImpersonate($entityManager, $user) {
        $builder = $entityManager->createQueryBuilder();
		$builder->select("u")
			->from("AppBundle\Entity\User", "u")
			->where("u != ?1")
			->setParameter(1, $user);
			
		$impersonatedUsersQuery = $builder->getQuery();
		return $impersonatedUsersQuery->getResult();	
    }

    public function getUserById($entityManager, $userId) {
        return $entityManager->find("AppBundle\Entity\User", $userId);
    }
}
?>