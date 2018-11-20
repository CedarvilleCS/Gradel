<?php

namespace AppBundle\Controller;

use AppBundle\Constants;

use AppBundle\Entity\Assignment;
use AppBundle\Entity\Course;
use AppBundle\Entity\User;
use AppBundle\Entity\UserSectionRole;

use AppBundle\Service\AssignmentService;
use AppBundle\Service\GraderService;
use AppBundle\Service\SectionService;
use AppBundle\Service\UserSectionRoleService;
use AppBundle\Service\UserService;

use AppBundle\Utils\Grader;
use AppBundle\Utils\Uploader;
use AppBundle\Utils\Zipper;

use \DateInterval;
use \DateTime;

use Psr\Log\LoggerInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends Controller {
    private $assignmentService;
    private $graderService;
    private $logger;
    private $sectionService;
    private $userSectionRoleService;
    private $userService;

    public function __construct(AssignmentService $assignmentService,
                                GraderService $graderService,
                                LoggerInterface $logger,
                                SectionService $sectionService,
                                UserSectionRoleService $userSectionRoleService,
                                UserService $userService) {
        $this->assignmentService = $assignmentService;
        $this->graderService = $graderService;
        $this->logger = $logger;
        $this->sectionService = $sectionService;
        $this->userSectionRoleService = $userSectionRoleService;
        $this->userService = $userService;
    }
    
    public function homeAction() {
        $user = $this->userService->getCurrentUser();
          if (!get_class($user)) {
            return $this->returnForbiddenResponse("USER DOES NOT EXIST");
        }
        
        //We want to be able to get the non deleted sections by date
        /* get all of the non-deleted sections
           they must start in at least 30 days and have ended at most 14 days ago to show up*/
        $sectionsActive = $this->sectionService->getNonDeletedSectionsForHome();
      
        /* get the user section role entities using the user entity and active sections */
        $userSectionRoles = $this->userSectionRoleService->getUserSectionRolesForHome($user, $sectionsActive);
        
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
        
        $assignments = $this->assignmentService->getAssignmentsSortedByDueDate($sections);
        $usersToImpersonate = $this->userService->getUsersToImpersonate($user);
        
        $grades = $this->graderService->getAllSectionGrades($user);
        
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

    private function returnForbiddenResponse($message){		
        $response = new Response($message);
        $response->setStatusCode(Response::HTTP_FORBIDDEN);
        $this->logError($message);
        return $response;
    }
    
    private function logError($message) {
        $errorMessage = "HomeController: ".$message;
        $this->logger->error($errorMessage);
        return $errorMessage;
    }
}
?>