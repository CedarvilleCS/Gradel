<?php

namespace AppBundle\Service;

use AppBundle\Constants;

use Symfony\Component\DependencyInjection\ContainerInterface;

class UserSectionRoleService
{
    private $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function getUserSectionRolesForHome($entityManager, $user, $sections) {
        $builder = $entityManager->createQueryBuilder();
		$builder->select("usr")
			->from("AppBundle\Entity\UserSectionRole", "usr")
			->where("usr.user = ?1")
			->andWhere("usr.section IN (?2)")
			->setParameter(1, $user)
			->setParameter(2, $sections);

		$userSectionRoleQuery = $builder->getQuery();
		return $userSectionRoleQuery->getResult();
	}
	
	public function getUserSectionRolesForAssignment($entityManager, $user, $section) {
        $builder = $entityManager->createQueryBuilder();
		$builder->select("usr")
			->from("AppBundle\Entity\UserSectionRole", "usr")
			->where("usr.user = ?1")
			->andWhere("usr.section = ?2")
			->setParameter(1, $user)
			->setParameter(2, $section);

		$userSectionRoleQuery = $builder->getQuery();
		return $userSectionRoleQuery->getOneOrNullResult();
	}

	public function getUserSectionRolesForAssignmentEdit($entityManager, $section) {
        $takesRole = $entityManager->getRepository("AppBundle\Entity\Role")->findOneBy([
			"role_name" => Constants::TAKES_ROLE
		]));
		$builder = $entityManager->createQueryBuilder();
		$builder->select("u")
			  ->from("AppBundle\Entity\UserSectionRole", "u")
			  ->where("u.section = ?1")
			  ->andWhere("u.role = ?2")
			  ->setParameter(1, $section)
			  ->setParameter(2, $takesRole);
			  
		$userSectionRoleQuery = $builder->getQuery();
		return $userSectionRoleQuery->getResult();
	}
	
	public function getUserSectionRolesOfSection($entityManager, $section) {
		$builder = $entityManager->createQueryBuilder();
		$builder->select("usr")
		->from("AppBundle\Entity\UserSectionRole", "usr")
		->where("usr.section = ?1")
		->setParameter(1, $section);

		$userSectionRoleQuery = $builder->getQuery();
		return $userSectionRoleQuery->getResult();
	}
}
?>