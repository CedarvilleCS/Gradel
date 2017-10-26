<?php

namespace AppBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\Submission;


use AppBundle\Entity\Role;
use AppBundle\Entity\User;
use AppBundle\Entity\Team;
use AppBundle\Entity\Course;
use AppBundle\Entity\Section;
use AppBundle\Entity\Assignment;
use AppBundle\Entity\Problem;
use AppBundle\Entity\UserSectionRole;
use AppBundle\Entity\Testcase;
use AppBundle\Entity\Language;
use AppBundle\Entity\Gradingmethod;
use AppBundle\Entity\Filetype;
use AppBundle\Entity\Feedback;
use AppBundle\Entity\TestcaseResult;

use Psr\Log\LoggerInterface;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\Response;


class ProblemsController extends Controller {

    public function problemsAction($assignmentId=1, $problemId=1) {

		$em = $this->getDoctrine()->getManager();
		$problem_entity = $em->find("AppBundle\Entity\Problem", $problemId);
		echo $problem_entity->name;
		$currentProblemDescription = stream_get_contents($problem_entity->description);
    echo $currentProblemDescription;

		$qb_langs = $em->createQueryBuilder();
		$qb_langs->select('l')
				->from('AppBundle\Entity\Language', 'l');

    echo "stuff and things";

		$query_langs = $qb_langs->getQuery();
		$languages = $query_langs->getResult();

		return $this->render('courses/assignments/problems/index.html.twig', [
			'problem' => $problem_entity,
			'problemDescription' => $currentProblemDescription,
			'languages' => $languages,
		]);
    }
}

?>
