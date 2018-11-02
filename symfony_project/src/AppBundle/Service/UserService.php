<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UserService {
    private $container;
    private $entityManager;

    public function __construct(ContainerInterface $container,
                                EntityManagerInterface $entityManager) {
        $this->container = $container;
        $this->entityManager = $entityManager;
    }

    public function getAllUsers() {
        return $this->entityManager->getRepository("AppBundle\Entity\User")->findAll();
    }

    public function getCurrentUser() {
        return $this->container->get("security.token_storage")->getToken()->getUser();
    }

    public function getUsersToImpersonate($user) {
        $builder = $this->entityManager->createQueryBuilder();
		$builder->select("u")
			->from("AppBundle\Entity\User", "u")
			->where("u != ?1")
			->setParameter(1, $user);
			
		$impersonatedUsersQuery = $builder->getQuery();
		return $impersonatedUsersQuery->getResult();	
    }

    public function getUserById($userId) {
        return $this->entityManager->find("AppBundle\Entity\User", $userId);
    }
}
?>