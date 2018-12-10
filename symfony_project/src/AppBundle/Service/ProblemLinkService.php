<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManagerInterface;

use AppBundle\Entity\ProblemLink;

use \DateTime;
use \DateInterval;

class ProblemLinkService{
    private $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager) {
		$this->entityManager = $entityManager;
    }
    
    public function deleteProblemLinksByProblem($problem, $shouldFlush = true){
        $builder = $this->entityManager->createQueryBuilder();
		$builder->select("pl")
			->from("AppBundle\Entity\ProblemLinks", "pl")
			->where("q.problem = (?1)")
            ->setParameter(1, $problem);       
        $queries = $builder->getQuery()->getResult();

        foreach ($queries as $query){
            $this->deleteQuery($query, $shouldFlush);
        }

        if($shouldFlush){
            $this->entityManager->flush();
        }
    }    

    public function deleteQuery($query, $shouldFlush = true){
        $this->entityManager->remove($query);
        if($shouldFlush){
            $this->entity->flush();
        }
    }

}

?>
