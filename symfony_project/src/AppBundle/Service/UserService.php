<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use AppBundle\Entity\User;

class UserService {
    private $container;
    private $entityManager;

    public function __construct(ContainerInterface $container,
                                EntityManagerInterface $entityManager) {
        $this->container = $container;
        $this->entityManager = $entityManager;
    }

    public function createUser($username, $email) {
        return new User($username, $email);
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

    public function getUserByObject($findBy) {
        return $this->entityManager->getRepository("AppBundle\Entity\User")->findOneBy($findBy);
    }

    public function insertUser($user, $shouldFlush = true) {
        $this->entityManager->persist($user);
        if ($shouldFlush) {
            $this->entityManager->flush();
        }
    }
}
?>