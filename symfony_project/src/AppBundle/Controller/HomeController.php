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
use AppBundle\Service\SemesterService;
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
    private $semesterService;
    private $userSectionRoleService;
    private $userService;

    public function __construct(AssignmentService $assignmentService,
                                GraderService $graderService,
                                LoggerInterface $logger,
                                SectionService $sectionService,
                                SemesterService $semesterService,
                                UserSectionRoleService $userSectionRoleService,
                                UserService $userService) {
        $this->assignmentService = $assignmentService;
        $this->graderService = $graderService;
        $this->logger = $logger;
        $this->sectionService = $sectionService;
        $this->semesterService = $semesterService;
        $this->userSectionRoleService = $userSectionRoleService;
        $this->userService = $userService;
    }
    
    public function homeAction() {
        $user = $this->userService->getCurrentUser();
          if (!get_class($user)) {
            return $this->returnForbiddenResponse("USER DOES NOT EXIST");
        }
    
        $currentSemester = $this->semesterService->getCurrentSemester();

        /* get all of the non-deleted sections
           they must start in at least 30 days and have ended at most 14 days ago to show up*/
        $sectionsActive = $this->sectionService->getSectionsBySemester($currentSemester);;
      
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
            "semester" => $semester,
            "grades" => $grades,
            "user_impersonators" => $usersToImpersonate
        ]);
    }

    private function modifyHomePostAction($term, $year){
        $user = $this->userService->getCurrentUser();
        if (!get_class($user)) {
            return $this->returnForbiddenResponse("YOU ARE NOT LOGGED IN");
        }

        /* See which fields were included */
        $postData = $request->request->all();

        $sectionTerm = $postData["semester"];
        $sectionYear = $postData["year"];

        /*saves the semester for which we will render sections*/
        $semester = $this->semesterService->getSemesterByTermAndYear($sectionTerm, $sectionYear);
        $sectionsBySemester = $this->sectionService->getSectionsBySemester($semester);

        /*Validate the data*/
        if (!isset($sectionTerm) || !isset($sectionYear)) {
            return $this->returnForbiddenResponse("NOT EVERY REQUIRED FIELD WAS PROVIDED");
        } else {
            /* Validate the year */
            if (!is_numeric(trim($sectionYear))) {
                return $this->returnForbiddenResponse($sectionYear." IS NOT A VALID YEAR");
            }
             /*Validate the semester */
            if (trim($sectionTerm) != "Fall" && trim($sectionTerm) != "Spring" && trim($sectionTerm) != "Summer") {
                return $this->returnForbiddenResponse($sectionTerm." IS NOT A VALID SEMESTER");
            }
            /*Validate that there are sections in this semester*/
            if (!$semester || !$sectionsBySemester){
                return $this->returnForbiddenResponse($sectionTerm." ".$sectionYear." There are no sections for this term");
            }
        }

        /* get the user section role entities using the user entity and active sections */
        $userSectionRoles = $this->userSectionRoleService->getUserSectionRolesForHome($user, $sectionsBySemester);
        
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

        /*They May want to impersonate students from previous semesters*/
        $usersToImpersonate = $this->userService->getUsersToImpersonate($user);
        
        /*They will want to see grades from that semester*/
        $grades = $this->graderService->getAllSectionGrades($user);

        /* Redirect to the new home page */
        return $this->render("home/index.html.twig", [
            "user" => $user,
            "usersectionroles" => $userSectionRoles,
            "assignments" => $assignments,
            "sections_taking" => $sectionsTaking,
            "sections_teaching" => $sectionsTeaching,
            "semester" => $semester,
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
