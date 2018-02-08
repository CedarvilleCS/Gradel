<?php

namespace AppBundle\Topic   ;

use Gos\Bundle\WebSocketBundle\Topic\TopicInterface;
use Gos\Bundle\WebSocketBundle\Client\ClientManipulatorInterface;
use Gos\Bundle\WebSocketBundle\Client\ClientStorageInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\Topic;
use Gos\Bundle\WebSocketBundle\Router\WampRequest;

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
        // An event has 4 params: contestId, scope, [recipients], msg
        // [recipients] is optional if scope is global or a group
        // [recipients] must be an string[] (usernames of recipients)
        $users = $this->clientManipulator->getAll($topic);
        $user = $this->clientManipulator->getClient($connection);
        $emUser = $this->em->find('AppBundle\Entity\User', $user->getID());

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
            dump("Required to provide message!");
            die();
        }
        dump("Valid msg...");
        if ($section->isActive() == true) {
            dump($grader);
            $elevatedUser = $grader->isJudging($emUser, $section) || $emUser->hasRole("ROLE_SUPER") || $emUser->hasRole("ROLE_ADMIN");
            dump("Elevated User: " . $elevatedUser);
            if ($scope == "global" && $elevatedUser == true) {
                dump("Sending a message to everyone...");
                $topic->broadcast(['msg' => $msg]);
            }
            else if ($scope == "userSpecific" && $elevatedUser == true) {
                if ($recipients == null) {
                    dump("Required to provide recipients!");
                    die();
                }
                // Send message to only specified users
                // URGENT: currently un-tested!!
                dump("Sending message to specific users...");
                foreach ($recipients as $r) {
                    $student = $this->clientManipulator->findByUsername($r);
                    $student->event(['msg' => $msg]);
                }

            }
            else if ($scope == "question") {
                // send message only to admins...
                dump("Searching for admins");
                foreach($users as $u) {
                    // find the judge id, get them in the em, and send them an event
                    $potJudge = $this->em->find("AppBundle\Entity\User", $u['client']->getID());
                    if ($grader->isJudging($potJudge, $section)) {
                        dump($potJudge->username . " is a judge");
                    }

                }

            }
        }

        

        // Search users that the message should go to (correct contest, group[student | admin | allStudents])

        // broadcast to only the correct users


    }

    /**
    * Like RPC is will use to prefix the channel
    * @return string
    */
    public function getName()
    {
        return 'appbundle.topic';
    }

    public function isJudging($user, $section){
		
		$role = $this->em->getRepository('AppBundle\Entity\Role')->findOneBy(array('role_name' => 'Judges'));		
		
		$qb = $this->em->createQueryBuilder();
		$qb->select('usr')
			->from('AppBundle\Entity\UserSectionRole', 'usr')
			->where('usr.role = ?1')
			->andWhere('usr.user = ?2')
			->andWhere('usr.section = ?3')
			->setParameter(1, $role)
			->setParameter(2, $user)
			->setParameter(3, $section);
			
		$query = $qb->getQuery();
		$usr = $query->getOneOrNullResult();
		
		return $usr->section == $section;		
	}
}