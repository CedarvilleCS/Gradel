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

    public function createEmptyQuery() {
        return new Query();
    }
    
    public function deleteQueriesByProblem($problem, $shouldFlush = true){
        $builder = $this->entityManager->createQueryBuilder();
		$builder->select("q")
			->from("AppBundle\Entity\Query", "q")
			->where("q.problem = (?1)")
            ->setParameter(1, $problem);       
        $queries = $builder->getQuery()->getResult();

        foreach ($queries as $query){
            $this->deleteQuery($query, $shouldFlush);
        }

        if ($shouldFlush) {
            $this->entityManager->flush();
        }
    }    

    public function deleteQueriesByProblemsAndAssignment($problems, $assignment) {
        $builder = $this->entityManager->createQueryBuilder();
        $builder->delete("AppBundle\Entity\Query", "q")
            ->where("q.problem IN (?1)")
            ->orWhere("q.assignment = (?2)")
            ->setParameter(1, $problems)
            ->setParameter(2, $assignment);
        
        $query = $builder->getQuery();
        return $query->getResult();
    }

    public function deleteQuery($query, $shouldFlush = true){
        $this->entityManager->remove($query);
        if($shouldFlush){
            $this->entityManager->flush();
        }
    }

    public function getQueryById($queryId) {
        return $this->entityManager->find("AppBundle\Entity\Query", $queryId);
    }

    public function insertQuery($query, $shouldFlush = true) {
        $this->entityManager->persist($query);
        if ($shouldFlush) {
            $this->entityManager->flush();
        }
    }
}

?>
