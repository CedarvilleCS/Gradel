<?php

namespace AppBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;

use AppBundle\Entity\Language;

class LanguageService 
{
    private $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }
    
    public function getLanguageById($entityManager, $languageId) {
        return $entityManager->find("AppBundle\Entity\Language", $languageId);
    }

    public function getAll($entityManager) {
        return $entityManager->getRepository("AppBundle\Entity\Language")->findAll();
    }
}
?>
