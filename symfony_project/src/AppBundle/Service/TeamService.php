<?php

namespace AppBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;

use \DateTime;
use \DateInterval;

class TeamService
{
    private $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
	}

	public function insertTeam($entityManager, $team) {
		$entityManager->persist($team);
	}
	
	public function getTeam($entityManager, $user, $assignment) {
		# get all of the teams
		$builder = $entityManager->createQueryBuilder();
		$builder->select('t')
				->from('AppBundle\Entity\Team', 't')
				->where('t.assignment = ?1')
				->setParameter(1, $assignment);
				
		$teamQuery = $builder->getQuery();
		$teams = $teamQuery->getResult();
		
		# loop over all the teams for this assignment and figure out which team the user is a part of
		foreach ($teams as $team) {
			foreach ($team->users as $teamUser) {
				if ($user->id == $teamUser->id) {
					return $team;
				}
			}
		}
		return null;
	}

	public function getTeamById($entityManager, $teamId) {
		return $entityManager->find("AppBundle\Entity\Team", $teamId);
	}
}
?>