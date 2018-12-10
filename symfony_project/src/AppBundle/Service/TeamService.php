<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManagerInterface;

use \DateTime;
use \DateInterval;

use AppBundle\Entity\Team;

class TeamService {
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
	}

	public function createEmptyTeam() {
		return new Team();
	}

	public function deleteTeam($team, $shouldFlush = true) {
		$this->entityManager->remove($team);
		if ($shouldFlush) {
			$this->entityManager->flush();
		}
	}

	public function getTeam($user, $assignment) {
		# get all of the teams
		$builder = $this->entityManager->createQueryBuilder();
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
	
	public function getTeamById($teamId) {
		return $this->entityManager->find("AppBundle\Entity\Team", $teamId);
	}

	public function getTeamsForSectionSearch($user) {
		$builder = $this->entityManager->createQueryBuilder()
			->select("t")
			->from("AppBundle\Entity\Team", "t")
			->where(":user MEMBER OF t.users")
			->setParameter("user", $user);
		$teamQuery = $builder->getQuery();
		return $teamQuery->getResult();
	}

	public function insertTeam($team, $shouldFlush = true) {
		$this->entityManager->persist($team);
		if ($shouldFlush) {
			$this->entityManager->flush();
		}
	}
}
?>
