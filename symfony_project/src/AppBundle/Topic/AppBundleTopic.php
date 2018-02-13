<?php

namespace AppBundle\Topic   ;

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

        $topic->broadcast(['msg' => $this->numUsers . ' total users']);
        
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
        $topic->broadcast(['msg' => $connection->resourceId . " has left " . $topic->getId()]);
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
        dump("USER:" . $this->clientManipulator->getClient($connection));
        $isNotController = is_object($this->clientManipulator->getClient($connection));
        $key = "null";
        if (is_array($event) != true) {
            $event = json_decode($event, true);
        }
        dump($event);
        if (array_key_exists("passKey", $event)){
            $key = $event["passKey"];
        }

        if ($isNotController || $key == "gradeldb251") {
            dump($event);
            $users = $this->clientManipulator->getAll($topic);
            $user = $this->clientManipulator->getClient($connection);
            if ($isNotController) {
                $emUser = $this->em->find('AppBundle\Entity\User', $user->getID());
            }
            
            $contestId = $event["contestId"];
            $scope = $event["scope"];
            $recipients = $event["recipients"];
            $msg = $event["msg"];

            $section = $this->em->find('AppBundle\Entity\Section', $contestId);

            // Look at the scope of the message and check user priveleges
            // Send message to all individuals who are allowed
            dump("Checking Scope and privleges...");
            $grader = new Grader($this->em);
            if ($msg == null) {
                exit("Required to provide message!");
            }
            if ($section->isActive() == true) {
                
                // Determine User Status
                $elevatedUser = false;
                if ($isNotController){
                    $elevatedUser = $grader->isJudging($emUser, $section) || $emUser->hasRole("ROLE_SUPER") || $emUser->hasRole("ROLE_ADMIN");
                }
                else {
                    $elevatedUser = ($key == "gradeldb251");
                }

                // Send correct message
                if ($scope == "global" && $elevatedUser == true) {
                    dump("Sending a message to everyone...");
                    $message = $this->buildMessage($msg, "notice");
                    $this->broadcastMessage(null, $topic, $users, $message);
                }
                else if ($scope == "pageUpdate" && $elevatedUser == true) {
                    $message = $this->buildMessage("null", "updateData");
                    $this->broadcastMessage(null, $topic, $users, $message);
                }
                else if (($scope == "userSpecificReject" || $scope = "userSpecificClarify") && $elevatedUser == true) {
                    if ($recipients == null) {
                        dump("Required to provide recipients!");
                    }
                    else {
                        $finalScope = $scope == "userSpecificReject" ? "reject" : "notice";
                        $message = $this->buildMessage($msg, $finalScope);
                        dump("Sending message: " . $message . "to specific users...");
                        $this->broadcastMessage($recipients, $topic, $users, $message);
                    }
                }
                else if ($scope == "question") {
                    dump("Searching for admins...");
                    $message = $this->buildMessage($msg, "notice");
                    $recipients = $this->getJudgeList($users);
                    $this->broadcastMessage($recipients, $topic, $users, $message);
                }
                else {
                    dump("No Scope Match Found!!");
                }
            }

        }

        // Search users that the message should go to (correct contest, group[student | admin | allStudents])

        // broadcast to only the correct users


    }

    public function broadcastMessage($recipients, $topic, $users, $message) {
        $nonGlobal = count($recipients) > 0;
        foreach($users as $u) {
            if ($nonGlobal) {
                $person = $this->em->find("AppBundle\Entity\User", $u['client']->getID());                
                if (in_array($person->getUsername(), $recipients)) {
                    dump("Sending to " . $person->getUsername());
                    dump($message);
                    $topic->broadcast($message, array(), array($u['connection']->WAMP->sessionId));
                }
            } 
            else {
                $topic->broadcast($message, array(), array($u['connection']->WAMP->sessionId));
            }
        }
    }

    public function getJudgeList($users) {
        $recipients = [];
        foreach($users as $u) {
            $potJudge = $this->em->find("AppBundle\Entity\User", $u['client']->getID());
            if ($grader->isJudging($potJudge, $section)) {
                array_push($potJudge->getUsername());
            }
        }
        return $recipients;
    }

    public function buildMessage($msg, $type) {
        return '{"msg": "' . $msg . '", "type": "'. $type . '"}';
    }


    public function onPush(Topic $topic, WampRequest $request, $data, $provider)
    {
        dump("Doing a push");
        dump ("REQUEST");
        dump($request);
        dump("END REQUEST");
        dump("DATA");
        dump($data);
        dump("END DATA");
        dump("PROVIDER");
        dump($provider);
        dump("END PROVIDER");
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