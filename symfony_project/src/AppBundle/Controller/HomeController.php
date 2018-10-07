<?php

namespace AppBundle\Controller;

use AppBundle\Constants;

use AppBundle\Entity\User;
use AppBundle\Entity\Course;
use AppBundle\Entity\UserSectionRole;
use AppBundle\Entity\Assignment;

use AppBundle\Service\AssignmentService;
use AppBundle\Service\SectionService;
use AppBundle\Service\UserSectionRoleService;
use AppBundle\Service\UserService;

use AppBundle\Utils\Grader;
use AppBundle\Utils\Uploader;
use AppBundle\Utils\Zipper;

use \DateTime;
use \DateInterval;

use Psr\Log\LoggerInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends Controller {
	private $assignmentService;
	private $logger;
	private $sectionService;
	private $userSectionRoleService;
	private $userService;

	public function __construct(AssignmentService $assignmentService,
								LoggerInterface $logger,
								SectionService $sectionService,
								UserSectionRoleService $userSectionRoleService,
								UserService $userService) {
		$this->assignmentService = $assignmentService;
		$this->logger = $logger;
		$this->sectionService = $sectionService;
		$this->userSectionRoleService = $userSectionRoleService;
		$this->userService = $userService;
	}
	
    public function homeAction() {		
		$entityManager = $this->getDoctrine()->getManager();
	  
		$user = $this->userService->getCurrentUser();
	  	if (!get_class($user)) {
			  $this->logger->error("HomeController: USER DOES NOT EXIST");
			  return $this->redirectToRoute("user_login");
		}
		
		/* get all of the non-deleted sections
		   they must start in at least 30 days and have ended at most 14 days ago to show up*/
		$sectionsActive = $this->sectionService->getNonDeletedSectionsForHome($entityManager);
	  
		/* get the user section role entities using the user entity and active sections */
		$userSectionRoles = $this->userSectionRoleService->getUserSectionRolesForHome($entityManager, $user, $sectionsActive);
		
		$sections = [];
		$sectionsTaking = [];
		$sectionsTeaching = [];
		foreach ($userSectionRoles as $userSectionRole){
			$sections[] = $userSectionRole->section->id;
			
			if ($userSectionRole->role->role_name == Constants::TAKES_ROLE) {
				$sectionsTaking[] = $userSectionRole->section;
			} else if ($userSectionRole->role->role_name == Constants::TEACHES_ROLE || 
			           $userSectionRole->role->role_name == Constants::JUDGES_ROLE) {
				$sectionsTeaching[] = $userSectionRole->section;
			}
		}
		
		$assignments = $this->assignmentService->getAssignmentsSortedByDueDateForHome($entityManager, $sections);
		$usersToImpersonate = $this->userService->getUsersToImpersonate($entityManager, $user);
		
		$grader = new Grader($entityManager);		
		$grades = $grader->getAllSectionGrades($user);
		
		return $this->render("home/index.html.twig", [
			"user" => $user,
			"usersectionroles" => $userSectionRoles,
			"assignments" => $assignments,
			"sections_taking" => $sectionsTaking,
			"sections_teaching" => $sectionsTeaching,
			"grades" => $grades,
			"user_impersonators" => $usersToImpersonate
		]);
    }
}
?>