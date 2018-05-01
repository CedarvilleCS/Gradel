<?php

namespace AppBundle\Topic;

use Gos\Bundle\WebSocketBundle\Topic\TopicInterface;
use Gos\Bundle\WebSocketBundle\Client\ClientManipulatorInterface;
use Gos\Bundle\WebSocketBundle\Client\ClientStorageInterface;

use Ratchet\ConnectionInterface;
use Ratchet\Wamp\Topic;
use Gos\Bundle\WebSocketBundle\Router\WampRequest;
use Gos\Bundle\WebSocketBundle\Topic\PushableTopicInterface;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Config\Definition\Exception\Exception;
use Doctrine\Common\Collections\ArrayCollection;

use AppBundle\Utils\Grader;
use AppBundle\Entity\Section;
use AppBundle\Entity\User;
use AppBundle\Entity\Role;
use AppBundle\Entity\Team;
use AppBundle\Entity\Course;
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


use Doctrine\ORM\EntityManager;

class AppBundleTopic implements TopicInterface
{
    protected $clientManipulator;
    protected $em;
    private $numUsers;

    /**
     * @param ClientManipulatorInterface $clientManipulator
     */
    public function __construct(ClientManipulatorInterface $clientManipulator, EntityManager $em)
    {
        $this->clientManipulator = $clientManipulator;
        $this->em = $em;
        $this->numUsers = 0;
    }
    /**
     * This will receive any Subscription requests for this topic.
     *
     * @param ConnectionInterface $connection
     * @param Topic $topic
     * @param WampRequest $request
     * @return void
     */
    public function onSubscribe(ConnectionInterface $connection, Topic $topic, WampRequest $request)
    {
        //this will broadcast the message to ALL subscribers of this topic.
        $user = $this->clientManipulator->getClient($connection);

        $this->numUsers += 1;

        //$topic->broadcast(['msg' => $this->numUsers . ' total users']);
        
    }

    /**
     * This will receive any UnSubscription requests for this topic.
     *
     * @param ConnectionInterface $connection
     * @param Topic $topic
     * @param WampRequest $request
     * @return void
     */
    public function onUnSubscribe(ConnectionInterface $connection, Topic $topic, WampRequest $request)
    {
        //this will broadcast the message to ALL subscribers of this topic.
        $this->numUsers -= 1;

        //$topic->broadcast(['msg' => $connection->resourceId . " has left " . $topic->getId()]);
    }


    /**
     * This will receive any Publish requests for this topic.
     *
     * @param ConnectionInterface $connection
     * @param Topic $topic
     * @param WampRequest $request
     * @param $event
     * @param array $exclude
     * @param array $eligible
     * @return mixed|void
     */
    public function onPublish(ConnectionInterface $connection, Topic $topic, WampRequest $request, $event, array $exclude, array $eligible)
    {
        $this->em->clear();
        
        $user = $this->clientManipulator->getClient($connection);

        if (!is_array($event)){
           $event = json_decode($event, true);
        }

        $contestId = $event["contestId"];

        if(!isset($contestId)){
            dump("No contest id was provided!");
            return;
        }

        $contest = $this->em->find('AppBundle\Entity\Assignment', $contestId);

        if(!$contest){
            dump("Contest does not exist!");
            return;
        }
      
        $type = $event['type'];
        $key = $event["passKey"];

        if( !(isset($key) && isset($type)) ){
            dump("key and type not provided");
            return;
        }

        if(isset($user->id)){
            $user = $this->em->find('AppBundle\Entity\User', $user->id);

            if(!isset($user)){
                dump("user is null");
                return;
            }
            
        } else if($key != "gradeldb251") {
            dump("Unable to get user and password is incorrect");
            return;
        }

        $grader = new Grader($this->em);

        if( $key != "gradeldb251" && (!isset($user) || !method_exists($user, 'hasRole') || !($grader->isTaking($user, $contest->section) || $grader->isJudging($user, $contest->section) || $user->hasRole("ROLE_SUPER"))) ){
            dump("Not allowed to access this");
            return;
        }

        // for inter-controller communication
        if($key == "gradeldb251"){

            $recipients = $event["recipients"];
            $msg = $event["msg"];


            if(!is_array($recipients) || count($recipients) < 1){
                dump("No recipients were provided! Returning...");
                return;
            }

            // send the message
            $message = $this->buildMessage($msg, $type);

            $this->broadcastMessage($recipients, $topic, $message);
        } 
        // for responses from a connection
        else if(isset($user)){

            # switch based on type
            
            // requesting scoreboard update
            if($type == "scoreboard"){
                
              if($contest->leaderboard){
                // send the scoreboard info 
                if($user->hasRole("ROLE_SUPER") || $grader->isJudging($user, $contest->section)){
                  $leaderboard = $contest->leaderboard->getJSONElevatedBoard();
                } else {
                  $leaderboard = $contest->leaderboard->getJSONBoard();
                }

                $this->broadcastMessage([$user->getUsername()], $topic, $this->buildMessage($leaderboard, 'scoreboard')); 
              }
            } 
            // requesting if contest has started
            else if($type == "check-start"){

                // see if the contest has started
                if($contest->isOpened()){

                    $contest->updateLeaderboard($grader, $this->em);

                    $this->broadcastMessage([$user->getUsername()], $topic, $this->buildMessage(null, 'start'));
                }
                // else do nothing

            }
            // requesting if contest is frozen
            else if($type == "check-frozen"){

                // see if the contest is frozen
                if($contest->isFrozen()){
                    $this->broadcastMessage([$user->getUsername()], $topic, $this->buildMessage(null, 'freeze'));
                }
                // else do nothing

            }
            // requesting time variables
            else if($type == "check-vars"){
                
                $times = [];

                $times['start'] = $contest->start_time->format('U');
                $times['end'] = $contest->end_time->format('U');
                $times['freeze'] = $contest->freeze_time->format('U');

                $this->broadcastMessage([$user->getUsername()], $topic, $this->buildMessage($times, 'vars'));
            }
            // requesting clarifications
            else if($type == "clarifications"){

              				
              # get the queries
              if($grader->isJudging($user, $contest->section) || $user->hasRole("ROLE_SUPER")){
                $extra_query = "OR 1=1";
                $team = null;
              } else {
                $extra_query = "";
                $team = $grader->getTeam($user, $contest);
              }

              // send the clarifications
              $qb_queries = $this->em->createQueryBuilder();
              $qb_queries->select('q')
                ->from('AppBundle\Entity\Query', 'q')
                ->where('q.assignment = (?1)')
                ->andWhere('q.asker = ?2 OR q.asker IS NULL '.$extra_query)
                ->orderBy('q.timestamp', 'ASC')
                ->setParameter(1, $contest)
                ->setParameter(2, $team);
              $query_query = $qb_queries->getQuery();
              $queries = $query_query->getResult();

              $this->broadcastMessage([$user->getUsername()], $topic, $this->buildMessage($queries, 'clarifications'));

            }
            // requesting list of problems
            else if($type == "problem-nav"){
                
                $problems = [];

                $elevated = $user->hasRole("ROLE_SUPER") || $grader->isJudging($user, $contest->section);
              
                $team = $grader->getTeam($user, $contest);

                if($contest->isOpened() || $elevated){
                    foreach($contest->problems as $prob){
                        
                        $problem = [];

                        $problem['id'] = $prob->id;
                        $problem['name'] = $prob->name;

                        $problems[] = $problem;
                    }
                }

                return $this->broadcastMessage([$user->getUsername()], $topic, $this->buildMessage($problems, 'problem-nav'));
            }
            // requesting problems
            else if($type == "checklist"){

              // send a list of problems
              $checklist = [];
             
              $elevated = $user->hasRole("ROLE_SUPER") || $grader->isJudging($user, $contest->section);
              
              $team = $grader->getTeam($user, $contest);

              if($contest->isOpened() || $elevated){
                foreach($contest->problems as $prob){

                  $problem = [];

                  if($team){
                    $score = $grader->getProblemScore($team, $prob, true);
                  } else {
                    $score = null;
                  }

                  $problem['id'] = $prob->id;
                  $problem['name'] = $prob->name;

                  $problem['submission_status'] = "unattempted";
                  $problem['penattempt'] = "";

                  if(isset($score) && $score['num_attempts'] > 0){
                    $problem['submission_status'] = "attempted";

                    if($score['correct']){
                        $problem['submission_status'] = "accepted";
                        $problem['penattempt'] = $score['time']." + ".$score['penalty_points_raw'];
                    }
                  }
                    
                  $checklist[] = $problem;
                }
              }
              
              
              return $this->broadcastMessage([$user->getUsername()], $topic, $this->buildMessage($checklist, 'checklist'));
            }
            // error
            else {
              // error
            }
        }
    }

    public function broadcastMessage($recipients, $topic, $message) {

        $users = $this->clientManipulator->getAll($topic);

        foreach($users as $u) {
            
            $person = $this->em->find("AppBundle\Entity\User", $u['client']->getID());          

            if (in_array($person->getUsername(), $recipients)) {

                dump("Sending message to ".$person->getUsername());
                $topic->broadcast($message, [], [$u['connection']->WAMP->sessionId]);

            }
        } 
    }

    public function buildMessage($msg, $type, $submissionId = -1) {
       
        $message = [];
       
        $message['msg'] = $msg;
        $message['type'] = $type;
        
        if($submissionId > 0){
            $message['submissionId'] = $submissionId;
        }

        return json_encode($message);
    }


    public function onPush(Topic $topic, WampRequest $request, $data, $provider)
    {
        dump("Pushing");
    }

    /**
    * Like RPC is will use to prefix the channel
    * @return string
    */
    public function getName()
    {
        return 'appbundle.topic';
    }
}