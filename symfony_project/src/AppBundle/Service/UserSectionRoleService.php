<?php

namespace AppBundle\Service;

use AppBundle\Constants;

use Doctrine\ORM\EntityManagerInterface;

class UserSectionRoleService {
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
    }

    public function getUserSectionRolesForHome($user, $sections) {
        $builder = $this->entityManager->createQueryBuilder();
		$builder->select("usr")
			->from("AppBundle\Entity\UserSectionRole", "usr")
			->where("usr.user = ?1")
			->andWhere("usr.section IN (?2)")
			->setParameter(1, $user)
			->setParameter(2, $sections);

		$userSectionRoleQuery = $builder->getQuery();
		return $userSectionRoleQuery->getResult();
	}
	
	public function getUserSectionRolesForAssignment($user, $section) {
        $builder = $this->entityManager->createQueryBuilder();
		$builder->select("usr")
			->from("AppBundle\Entity\UserSectionRole", "usr")
			->where("usr.user = ?1")
			->andWhere("usr.section = ?2")
			->setParameter(1, $user)
			->setParameter(2, $section);

		$userSectionRoleQuery = $builder->getQuery();
		return $userSectionRoleQuery->getOneOrNullResult();
	}

	public function getUserSectionRolesForAssignmentEdit($section) {
        $takesRole = $this->entityManager->getRepository("AppBundle\Entity\Role")->findOneBy([
			"role_name" => Constants::TAKES_ROLE
		]);
		$builder = $this->entityManager->createQueryBuilder();
		$builder->select("u")
			  ->from("AppBundle\Entity\UserSectionRole", "u")
			  ->where("u.section = ?1")
			  ->andWhere("u.role = ?2")
			  ->setParameter(1, $section)
			  ->setParameter(2, $takesRole);
			  
		$userSectionRoleQuery = $builder->getQuery();
		return $userSectionRoleQuery->getResult();
	}
	
	public function getUserSectionRolesOfSection($section) {
		$builder = $this->entityManager->createQueryBuilder();
		$builder->select("usr")
		->from("AppBundle\Entity\UserSectionRole", "usr")
		->where("usr.section = ?1")
		->setParameter(1, $section);

		$userSectionRoleQuery = $builder->getQuery();
		return $userSectionRoleQuery->getResult();
	}
}
?>