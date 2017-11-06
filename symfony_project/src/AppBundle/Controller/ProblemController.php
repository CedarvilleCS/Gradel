<?php

namespace AppBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use AppBundle\Entity\Submission;
use AppBundle\Entity\Problem;
use AppBundle\Entity\ProblemLanguage;
use AppBundle\Entity\UserSectionRole;

use AppBundle\Utils\Grader;

use Psr\Log\LoggerInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class ProblemController extends Controller {

   
    public function newAction($sectionId, $assignmentId) {
      return $this->render('problem/new.html.twig', [
        'sectionId' => $sectionId,
        'assignmentId' => $assignmentId,
      ]);
    }

    public function editAction() {
      return $this->render('problem/edit.html.twig', [

      ]);
    }
	
	public function resultAction($submission_id) {
		
		$em = $this->getDoctrine()->getManager();
		
		$submission = $em->find("AppBundle\Entity\Submission", $submission_id);	
		
		if(!submission){
			echo "SUBMISSION DOES NOT EXIST";
			die();
		}
		
		$compiler_output = stream_get_contents($submission->compiler_output);
		$submission_file = stream_get_contents($submission->submitted_file);
		
		foreach($submission->testcaseresults as $tc){
			
			$output["std_output"] = stream_get_contents($tc->std_output);
			$output["runtime_output"] = stream_get_contents($tc->runtime_output);
			$output["time_output"] = $tc->execution_time;
			$tc_output[] = $output;	
		}
					
        return $this->render('problem/result.html.twig', [
			'submission' => $submission,
			'problem' => $submission->problem,
			'grader' => new Grader($em),
			
			'testcases_output' => $tc_output,
			'compiler_output' => $compiler_output,
			'submission_file' => $submission_file,
        ]);	
	}
}

?>
