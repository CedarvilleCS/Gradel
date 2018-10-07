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
        return $this->container->get('security.token_storage')->getToken()->getUser();
    }
}
?>