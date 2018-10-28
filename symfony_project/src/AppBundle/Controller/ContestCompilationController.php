<?php

namespace AppBundle\Controller;

use \DateTime;
use \DateInterval;

use AppBundle\Entity\Assignment;
use AppBundle\Entity\Course;
use AppBundle\Entity\Leaderboard;
use AppBundle\Entity\Problem;
use AppBundle\Entity\ProblemLanguage;
use AppBundle\Entity\Query;
use AppBundle\Entity\Role;
use AppBundle\Entity\Section;
use AppBundle\Entity\Submission;
use AppBundle\Entity\Team;
use AppBundle\Entity\Testcase;
use AppBundle\Entity\User;
use AppBundle\Entity\UserSectionRole;

use AppBundle\Service\SubmissionService;
use AppBundle\Service\UserService;

use Symfony\Component\Config\Definition\Exception\Exception;

use AppBundle\Utils\Generator;
use AppBundle\Utils\Grader;
use AppBundle\Utils\SocketPusher;
use AppBundle\Utils\Uploader;
use AppBundle\Utils\Zipper;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Psr\Log\LoggerInterface;

class ContestCompilationController extends Controller {
    private $logger;
    private $submissionService;
    private $userService;

    public function __construct(LoggerInterface $logger,
                                SubmissionService $submissionService,
                                UserService $userService) {
        $this->logger = $logger;
        $this->submissionService = $submissionService;
        $this->userService = $userService;
    }

	public function contestSubmitAction(Request $request, $trialId = 0) {
        $entityManager = $this->getDoctrine()->getManager();
        $grader = new Grader($entityManager);
        
        /* Get the current user */
		$user = $this->userService->getCurrentUser($entityManager);	
		if (!get_class($user)) {
			return $this->returnForbiddenResponse("USER DOES NOT EXIST");
        }
        
        /* Forward to actual compilation controller */
        $response = $this->forward('AppBundle\Controller\CompilationController::submitAction', [
            'trialId' => $trialId,
            // password to allow the contest to run the submit controller
            'forwarded' => "secret_code",
        ]);

        $submissionId = json_decode($response->getContent())->submission_id;

        if (!$submissionId) {
            return $response;
        }

        $submission = $entityManager->find("AppBundle\Entity\Submission", $submissionId);

        if (!$submission->problem->assignment->post_contest && 
            !$submission->problem->assignment->pre_contest && 
            $grader->getTeam($user, $submission->problem->assignment) && 
            !$submission->isCorrect() && !$submission->isError()) {
            $submission->pending_status = 0;

            $this->submissionService->insertSubmission($entityManager, $submission);
        } 

        $contest = $submission->problem->assignment;
        
        /* SOCKET PUSHER UPDATE */
        $pusher = new SocketPusher($this->container->get('gos_web_socket.wamp.pusher'), $entityManager, $contest);
        if ($submission->pending_status != 0) {   
            /* UPDATE LEADERBOARD */
            $contest->updateLeaderboard($grader, $entityManager);
            $pusher->sendGradedSubmission($submission);     
            $pusher->sendScoreboardUpdates();
        } else {
            /* Send the ungraded one to the judges for grading */
		    $pusher->sendNewSubmission($submission);
        }
				
        $url = $this->generateUrl('contest_result', [
            'contestId' => $submission->problem->assignment->section->id,
            'roundId' => $submission->problem->assignment->id,
            'problemId' => $submission->problem->id,
            'resultId' => $submission->id,
        ]);

        $response = new Response(json_encode([		
			'redirect_url' => $url,	
			'submission_id' => $submission->id,		
        ]));

        return returnOkResponse($response);
    } 

	public function contestQuickAction(Request $request) {
		$response = $this->forward('AppBundle\Controller\TrialController::trialModifyAction');
				
		if ($response->getStatusCode() == Response::HTTP_OK) {
			return $this->forward('AppBundle\Controller\ContestCompilationController::contestSubmitAction', [
				'trialId' => json_decode($response->getContent())->trial_id,
			]);
		} else {			
			return $response;	
		}	
	}

    private function logError($message) {
		$errorMessage = "ContestCompilationController: ".$message;
		$this->logger->error($errorMessage);
		return $errorMessage;
	}
	
	private function returnForbiddenResponse($message){		
		$response = new Response($message);
		$response->setStatusCode(Response::HTTP_FORBIDDEN);
		$this->logError($message);
		return $response;
	}

	private function returnOkResponse($response) {
		$response->headers->set("Content-Type", "application/json");
		$response->setStatusCode(Response::HTTP_OK);
		return $response;
	}
}

?>