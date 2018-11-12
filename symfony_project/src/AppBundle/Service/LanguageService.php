<?php

namespace AppBundle\Service;

use AppBundle\Entity\Language;
use Doctrine\ORM\EntityManagerInterface;

class LanguageService {
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
    }
    
    public function getLanguageById($languageId) {
        return $this->entityManager->find("AppBundle\Entity\Language", $languageId);
    }

    public function getAll() {
        return $this->entityManager->getRepository("AppBundle\Entity\Language")->findAll();
    }
}
?>
