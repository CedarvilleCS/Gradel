<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManagerInterface;

use AppBundle\Entity\Role;

use \DateTime;
use \DateInterval;

class RoleService {

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
	}

	public function getRoleByRoleName($roleName) {
        return $this->entityManager->getRepository("AppBundle\Entity\Role")->findOneBy(array("role_name" => $roleName));
    }
}
?>