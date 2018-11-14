<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManagerInterface;

use AppBundle\Entity\Query;

use \DateTime;
use \DateInterval;

class QueryService{
    private $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager) {
		$this->entityManager = $entityManager;
    }
    
    public function deleteQueriesByProblem($problem, $shouldFlush){
        $builder = $this->entityManager->createQueryBuilder();
		$builder->select("q")
			->from("AppBundle\Entity\Query", "q")
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

    public function deleteQuery($query, $shouldFlush){
        $this->entityManager->remove($query);
        if($shouldFlush){
            $this->entity->flush();
        }
    }

}

?>