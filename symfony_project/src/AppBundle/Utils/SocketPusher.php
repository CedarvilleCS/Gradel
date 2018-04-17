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
use AppBundle\Entity\Query;

use \DateTime;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SocketPusher  {
	
	private $em;
	private $pusher;	
  private $contest;
  
	public function __construct($pusher, $em, $contest) {
    
    if(stripos(get_class($pusher), "WampPusher") === FALSE){
			throw new Exception('The Socket Pusher class must be given a WampPusher but was given '.get_class($pusher));
    }

    if(stripos(get_class($em), "EntityManager") === FALSE){
			throw new Exception('The Socket Pusher class must be given a EntityManager but was given '.get_class($em));
    }   
        
    $this->pusher = $pusher;
    $this->em = $em;
    $this->contest = $contest;
  }
  
  /* SENDERS */
  public function sendScoreboardUpdates() {

    # SEND TO THE PLEBS    
    if(!$this->contest->isFrozen()){

      $plebUsers= $this->getUsernamesFromUsers($this->contest->section->getRegularUsers());
      
      if(count($plebUsers) >= 1){
        $plebInfo = [
          'type' => 'scoreboard',
          'recipients' => $plebUsers,
          'msg' => $this->contest->leaderboard->getJSONBoard(),
          'passKey' => 'gradeldb251',
          'contestId' => $this->contest->id,
        ];

        $this->pusher->push($plebInfo, 'appbundle_topic', ['username'=>'user1']);
      }
    }

    # SEND TO THE KINGS     
    $kingUsers = $this->getUsernamesFromUsers($this->contest->section->getElevatedUsers());
    
    if(count($kingUsers) >= 1){

      $elevatedInfo = [
        'type' => 'scoreboard',
        'recipients' => $kingUsers,
        'msg' => $this->contest->leaderboard->getJSONElevatedBoard(),
        'passKey' => 'gradeldb251',
        'contestId' => $this->contest->id,
      ];

      $this->pusher->push($elevatedInfo, 'appbundle_topic', ['username'=>'user1']);
    }
  }

  // sends clarification to judges for answering
  public function sendNewClarification($query) {
        
    // list of people to send the clarification too
    $judges = $this->getUsernamesFromUsers($this->contest->section->getJudgeUsers());

    $newClarInfo = [
      'type' => 'new-clarification',
      'recipients' => $judges,
      'msg' => json_encode($query),
      'passKey' => 'gradeldb251',
      'contestId' => $this->contest->id,
    ];

    $this->pusher->push($newClarInfo, 'appbundle_topic', ['username'=>'user1']); 
  }

  // sends submissions to the judges for grading 
  public function sendNewSubmission($submission) {
        
    // list of people to send the clarification too
    $judges = $this->getUsernamesFromUsers($this->contest->section->getJudgeUsers());

    $newSubInfo = [
      'type' => 'new-submission',
      'recipients' => $judges,
      'msg' => json_encode($submission),
      'passKey' => 'gradeldb251',
      'contestId' => $this->contest->id,
    ];

    $this->pusher->push($newSubInfo, 'appbundle_topic', ['username'=>'user1']); 
  }

  public function sendClaimedSubmission($submissionId){

    // list of people to send the clarification too
    $judges = $this->getUsernamesFromUsers($this->contest->section->getJudgeUsers());

    $claimedSubInfo = [
      'type' => 'claimed-submission',
      'recipients' => $judges,
      'msg' => $submissionId,
      'passKey' => 'gradeldb251',
      'contestId' => $this->contest->id,
    ];

    $this->pusher->push($claimedSubInfo, 'appbundle_topic', ['username'=>'user1']); 

  }

  public function sendGradedSubmission($submission){
     
    // list of people to send the clarification too
     $judges = $this->getUsernamesFromUsers($this->contest->section->getJudgeUsers());

     $gradedSubInfo = [
       'type' => 'graded-submission',
       'recipients' => $judges,
       'msg' => json_encode($submission),
       'passKey' => 'gradeldb251',
       'contestId' => $this->contest->id,
     ];
 
     $this->pusher->push($gradedSubInfo, 'appbundle_topic', ['username'=>'user1']); 
  }

  public function sendClarification($query) {

    // send to the judges
    // list of people to send the clarification too
    $judges = $this->getUsernamesFromUsers($this->contest->section->getJudgeUsers());

    $ansClarInfo = [
      'type' => 'answered-clarification',
      'recipients' => $judges,
      'msg' => json_encode($query),
      'passKey' => 'gradeldb251',
      'contestId' => $this->contest->id,
    ];

    $this->pusher->push($ansClarInfo, 'appbundle_topic', ['username'=>'user1']); 


    // send to the normal people
    // whether to send this query to everyone
    $global = !isset($query->asker);
    
    // list of people to send the clarification too
    $clarUsers = [];

    if(!$global){
      foreach($query->asker->users as $user){
        $clarUsers[] = $user->getUsername();
      }
    } else {
      $clarUsers = $this->getUsernamesFromUsers($this->contest->section->getAllUsers());
    }

    $clarificationInfo = [
      'type' => 'clarification',
      'recipients' => $clarUsers,
      'msg' => $this->buildClarificationMessageFromQuery($query),
      'passKey' => 'gradeldb251',
      'contestId' => $this->contest->id,
    ];

    $this->pusher->push($clarificationInfo, 'appbundle_topic', ['username'=>'user1']);
  }

  public function sendResponse($submission, $type){

    $info = [
      'type' => $type,
      'recipients' => $this->getUsernamesFromTeam($submission->team),
      'msg' => $this->buildResponse($submission, $type),
      'passKey' => 'gradeldb251',
      'contestId' => $this->contest->id,
    ];

    $this->pusher->push($info, 'appbundle_topic', ['username'=>'user1']);
  }

  public function sendRefresh(){

    $refreshInfo = [
      'type' => 'refresh',
      'recipients' => $this->getUsernamesFromUsers($this->contest->section->getAllUsers()),
      'msg' => null,
      'passKey' => 'gradeldb251',
      'contestId' => $this->contest->id,
    ];

    $this->pusher->push($refreshInfo, 'appbundle_topic', ['username'=>'user1']); 
  }

  public function sendFreeze(){
    $refreshInfo = [
      'type' => 'freeze',
      'recipients' => $this->getUsernamesFromUsers($this->contest->section->getAllUsers()),
      'msg' => null,
      'passKey' => 'gradeldb251',
      'contestId' => $this->contest->id,
    ];

    $this->pusher->push($refreshInfo, 'appbundle_topic', ['username'=>'user1']); 
  }

  public function sendUnfreeze(){
    $refreshInfo = [
      'type' => 'unfreeze',
      'recipients' => $this->getUsernamesFromUsers($this->contest->section->getAllUsers()),
      'msg' => null,
      'passKey' => 'gradeldb251',
      'contestId' => $this->contest->id,
    ];

    $this->pusher->push($refreshInfo, 'appbundle_topic', ['username'=>'user1']); 
  }

  /* HELPERS */
	public function getUsernamesFromTeam($team) {
		
		$recipients = [];
		foreach($team->users as $user) {
			$recipients[] = $user->getUsername();
		}

		return $recipients;
  }
  
  public function getUsernamesFromUsers($users){
    $names = [];

    foreach($users as $user){
      $names[] = $user->getUsername();
    }

    return $names;
  }

  public function buildResponse($submission, $type){

    $id = $submission->id;

    $custom = "";
    if($submission->judge_message && $submission->judge_message != ""){
      $custom = "\\nJudge Message: ".htmlspecialchars($submission->judge_message);
    }

    $result = $submission->getResultString();
    
    $message = "Your submission for ".htmlspecialchars($submission->problem->name);

    if($type != "delete"){
      $message .= " was judged <b>".$result."</b>.".$custom;
    } else {
      $message .= " was <b> deleted </b>";
    }

    return [
      'id' => $id,
      'message' => $message,
    ];
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