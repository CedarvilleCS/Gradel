<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Testcase;
use AppBundle\Entity\Problem;
use AppBundle\Entity\Role;
use AppBundle\Entity\Submission;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /* name=homepage */
    public function indexAction(Request $request) {
				
		
    }
	
	
	/* name=problem */
	public function problemAction($problem_id=1) {
		
		$team_id = 1;
		$web_dir = $this->get('kernel')->getProjectDir();
		$date_format = "H:i:s m/d/Y";
			
		
		$em = $this->getDoctrine()->getManager();
		
		$qb = $em->createQueryBuilder();
		
		$qb->select('p')
			->from('AppBundle\Entity\Problem', 'p')
			->where('p.id = ?1')
			->setParameter(1, $problem_id);
			
		$query = $qb->getQuery();
		$problem_original = $query->getSingleResult();
		
		$problem = $problem_original;
		
		$problem->description = stream_get_contents($problem->description);
		$problem->instructions = stream_get_contents($problem->instructions);
		
		$problem->default_code = stream_get_contents($problem->default_code);

		# make the directory for the temporary stuff
		$temp_folder = $web_dir."/compilation/temp/".$team_id."/";
		
		shell_exec("mkdir -p ".$temp_folder);
		
		# save the input/output files to a temp folder
		# deblobinate the input/output files
		foreach($problem->testcases as &$tc){
			
			// write the input file to the temp directory
			$tc->input = stream_get_contents($tc->input);			
			$file = fopen($temp_folder.$tc->seq_num.".in", "w") or die("Unable to open file for writing!");
			fwrite($file, $tc->input);
			fclose($file);
			
			// write the output file to the temp directory
			$tc->correct_output = stream_get_contents($tc->correct_output);
			$file = fopen($temp_folder.$tc->seq_num.".out", "w") or die("Unable to open file for writing!");
			fwrite($file, $tc->correct_output);
			fclose($file);
			
			// save the feedback blobs			
			if(is_resource($tc->feedback->short_response) && get_resource_type($tc->feedback->short_response) == "stream"){
				$tc->feedback->short_response = stream_get_contents($tc->feedback->short_response);
			}
			if(is_resource($tc->feedback->long_response) && get_resource_type($tc->feedback->long_response) == "stream"){
				$tc->feedback->long_response = stream_get_contents($tc->feedback->long_response);
			}
		}		
        
		
		# get the current team
		$qb_team = $em->createQueryBuilder();
		$qb_team->select('t')
				->from('AppBundle\Entity\Team', 't')
				->where('t.id = ?1')
				->setParameter(1, $team_id);
				
		$query_team = $qb_team->getQuery();
		$team_original = $query_team->getSingleResult();
		
				
		/*
		# public function __construct11($prob, $tm, $time, $acc, $subm, $filetype, $mainclass, $compout, $didcomp, $lang, $perc){
		$submission = new Submission();
		
		$em->persist($submission);
		$em->flush();
		
		$submission->problem = $problem_original;
		$submission->team = $team_original;
		$submission->timestamp = date($date_format);	
		
		*/
		shell_exec("rm -rf ".$temp_folder);
		
        return $this->render('compilation/problem/index.html.twig', [
			'problem' => $problem,
        ]);
	}
}
