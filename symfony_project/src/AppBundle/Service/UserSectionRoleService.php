<?php

namespace AppBundle\Service;

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
}
?>