<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManagerInterface;

use AppBundle\Entity\Feedback;

class FeedbackService{

    private $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager) {
		$this->entityManager = $entityManager;
    }
    
    public function deleteFeedbacksByTestcase($testcase, $shouldFlush){
        $builder = $this->entityManager->createQueryBuilder();
		$builder->select("f")
			->from("AppBundle\Entity\Feedback", "f")
			->where("f = (?1)")
            ->setParameter(1, $testcase->feedback);       
        $feedbacks = $builder->getQuery()->getResult();

        foreach ($feedbacks as $feedback){
            $this->deleteFeedback($feedback, $shouldFlush);
        }

        if($shouldFlush){
            $this->entityManager->flush();
        }
    }    

    public function deleteFeedback($feedback, $shouldFlush = true){
        $this->entityManager->remove($feedback);
        if($shouldFlush){
            $this->entity->flush();
        }
    }

}

?>
