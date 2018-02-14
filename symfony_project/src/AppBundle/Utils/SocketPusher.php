<?php

namespace AppBundle\Utils;

use AppBundle\Entity\Role;
use AppBundle\Entity\User;
use AppBundle\Entity\Team;
use AppBundle\Entity\Course;
use AppBundle\Entity\Section;
use AppBundle\Entity\Assignment;
use AppBundle\Entity\Problem;
use AppBundle\Entity\ProblemLanguage;
use AppBundle\Entity\UserSectionRole;
use AppBundle\Entity\Testcase;
use AppBundle\Entity\Submission;
use AppBundle\Entity\Language;
use AppBundle\Entity\Feedback;
use AppBundle\Entity\TestcaseResult;

use \DateTime;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SocketPusher  {
	
	private $pusher;
	
	public function __construct($pusher) {
    
    if(get_class($pusher) != "Gos\Bundle\WebSocketBundle\Pusher\Wamp\WampPusher"){
			throw new Exception('The Grader class must be given a Gos\Bundle\WebSocketBundle\Pusher\Wamp\WampPusher but was given '.get_class($pusher));
		}

    $this->pusher = $pusher;
	}
  
  public function pushGlobalMessage($msg, $contestId) {
    $this->pusher->push([
      'contestId' => $contestId,
      'scope' => 'global',
      'recipients' => null,
      'msg' => $msg,
      'passKey' => 'gradeldb251'], 
      'appbundle_topic', ['username' => 'user1']);

  }	

  public function pushUserSpecificMessage($msg, $recipients, $contestId, $isErrorMessage) {
    $this->pusher->push([
      'contestId' => $contestId,
      'scope' => $isErrorMessage ? 'userSpecificReject' : 'userSpecificClarify',
      'recipients' => $recipients,
      'msg' => $msg,
      'passKey' => 'gradeldb251'], 
      'appbundle_topic', ['username' => 'user1']);
  }

  public function promptDataRefresh($contestId) {
    $this->pusher->push([
      'msg' => "null",
      'contestId' => $contestId,
      'scope' => 'pageUpdate',
      'recipients' => null,
      'passKey' => 'gradeldb251'],
      'appbundle_topic', ['username' => 'user1']);
  }

  public function getUsernamesFromTeam($team) {
    $recipients = [];
    foreach($team->users as $user) {
      array_push($recipients, $user->getUsername());
    }
    return $recipients;
  }

  public function buildRejection($submission) {
    return "Your submission for " . htmlspecialchars($submission->problem->name) . " was <b>incorrect</b>.";
  }

  public function buildAcceptance($submission) {
    return "Your submission for " . htmlspecialchars($submission->problem->name) . " was <b>correct</b> (Judge Override).";
  }

  public function buildCustomRejection($submission) {
    return "Your submission for " . htmlspecialchars($submission->problem->name) . " was <b>incorrect</b>. \\nJudge Message: " . htmlspecialchars($submission->judge_message);
  }

  public function buildFormattingRejection($submission) {
    return "Your submission for " . htmlspecialchars($submission->problem->name) . " was <b>incorrect</b>. \\nJudge Message: Formatting Error";
  }

  public function buildDeleteRejection($submission) {
    return "Your submission for " . htmlspecialchars($submission->problem->name) . " was deleted";
  }

  public function buildClarificationMessageFromQuery($query) {
    return $this->buildClarificationMessage($query->question, $query->answer, $query->problem->name);
  }

  public function buildClarificationMessage($question, $answer, $name) {
    if ($question == "") {
      return "<b>Notice:</b> " . htmlspecialchars($answer);
    }
    else {
      $problemName = $name ? "Question Concerning " . $name . ":" : "Question: ";
      return "<b>" . htmlspecialchars($problemName) . "</b> " . htmlspecialchars($question) . "\\n<b>Answer:</b> " . htmlspecialchars($answer);
    }
  }
}

?>