<?php

namespace AppBundle\Controller;

use AppBundle\Constants;

use AppBundle\Entity\Assignment;
use AppBundle\Entity\Course;
use AppBundle\Entity\Section;
use AppBundle\Entity\Team;
use AppBundle\Entity\Trial;
use AppBundle\Entity\User;
use AppBundle\Entity\UserSectionRole;

use AppBundle\Service\AssignmentService;
use AppBundle\Service\GraderService;
use AppBundle\Service\ProblemService;
use AppBundle\Service\SectionService;
use AppBundle\Service\SubmissionService;
use AppBundle\Service\TeamService;
use AppBundle\Service\TestCaseService;
use AppBundle\Service\TrialService;
use AppBundle\Service\UserSectionRoleService;
use AppBundle\Service\UserService;

use AppBundle\Utils\Uploader;

use Doctrine\Common\Collections\ArrayCollection;

use \DateInterval;
use \DateTime;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Psr\Log\LoggerInterface;

class AssignmentController extends Controller {
    private $assignmentService;
    private $graderService;
    private $logger;
    private $problemService;
    private $sectionService;
    private $submissionService;
    private $teamService;
    private $testCaseService;
    private $userSectionRoleService;
    private $userService;

    public function __construct(AssignmentService $assignmentService,
                                GraderService $graderService,
                                LoggerInterface $logger,
                                ProblemService $problemService,
                                SectionService $sectionService,
                                SubmissionService $submissionService,
                                TeamService $teamService,
                                TestCaseService $testCaseService,
                                TrialService $trialService,
                                UserSectionRoleService $userSectionRoleService,
                                UserService $userService) {
        $this->assignmentService = $assignmentService;
        $this->graderService = $graderService;
        $this->logger = $logger;
        $this->problemService = $problemService;
        $this->sectionService = $sectionService;
        $this->submissionService = $submissionService;
        $this->teamService = $teamService;
        $this->testCaseService = $testCaseService;
        $this->trialService = $trialService;
        $this->userSectionRoleService = $userSectionRoleService;
        $this->userService = $userService;
    }

    public function assignmentAction($sectionId, $assignmentId) {
        $user = $this->userService->getCurrentUser();
        $requestingUser = $this->getUser();
        if (!get_class($user)) {
            $this->returnForbiddenResponse("USER DOES NOT EXIST");
      }
        
        // get the section
        if (!isset($sectionId) || !($sectionId > 0)) {
            $this->returnForbiddenResponse("SECTION ID WAS NOT PROVIDED OR FORMATTED PROPERLY");
        }
        
        $section = $this->sectionService->getSectionById($sectionId);
        if (!$section) {
            $this->returnForbiddenResponse("SECTION ".$sectionId." DOES NOT EXIST");
        }
        
        // REDIRECT TO CONTEST IF NEED BE
        if ($section->course->is_contest) {
            if (!isset($problemId)) {
                return $this->redirectToRoute("contest", 
                [
                    "contestId" => $sectionId,
                    "roundId" => $assignmentId
                ]);
            } else {
                return $this->redirectToRoute("contest_problem", 
                [
                    "contestId" => $sectionId, 
                    "roundId" => $assignmentId, 
                    "problemId" => $problemId
                ]);
            }
        }
        
        // get the assignment
        if (!isset($assignmentId) || !($assignmentId > 0)) {
            $this->returnForbiddenResponse("ASSIGNMENT ID WAS NOT PROVIDED OR FORMATTED PROPERLY");
        }
        
        $assignment = $this->assignmentService->getAssignmentById($assignmentId);
        if (!$assignment) {
            $this->returnForbiddenResponse("ASSIGNMENT ".$assignmentId." DOES NOT EXIST");
        }

        if (!isset($assignmentId) || $assignmentId == 0) {
            return $this->returnForbiddenResponse("ASSIGNMENT ID NOT PROVIDED OR FORMATTED PROPERLY");
        }

        $assignment = $this->assignmentService->getAssignmentById($assignmentId);
        if (!$assignmentId) {
            return $this->returnForbiddenResponse("ASSIGNMENT ".$assignmentId." DOES NOT EXIST");
        }
        
        // get all the users taking the course
        $section = $this->sectionService->getSectionById($sectionId);
        $sectionTakerRoles = $this->userSectionRoleService->getUserSectionRolesForAssignmentEdit($section);
                
        $grades = [];
        $sectionTakers = [];
        foreach ($sectionTakerRoles as $sectionTakerRole) {
            $sectionTakers[] = $sectionTakerRole->user;
            $grades[$sectionTakerRole->user->id] = $this->graderService->getAssignmentGrade($sectionTakerRole->user, $assignment);
        }

        $assignmentProblems = $assignment->problems;
        foreach ($sectionTakers as $sectionTaker) {
            foreach ($assignmentProblems as $assignmentProblem) {
                $testCaseInfo = $this->testCaseService->getTestCaseInfoFromTeamOrUserAndProblem($requestingUser, "s.user = ?1", $assignmentProblem);
                if ($sectionTaker == $requestingUser) {
                    $assignmentProblem->userTestCaseInfo = $testCaseInfo;
                }
                if ($testCaseInfo->numberOfTestCases == $testCaseInfo->numberOfCorrectTestCases) {
                    $sectionTaker->numberOfCompletedProblems++;
                }
            }
        }

        return $this->render("assignment/index.html.twig", [
            "assignment" => $assignment,
            "section" => $section,
            "section_takers" => $sectionTakers,
            "user" => $user,
            "user_impersonators" => $sectionTakers,
            "grades" => $grades
        ]);
    }

    public function editAction($sectionId, $assignmentId) {
        if (!isset($sectionId) || !($sectionId > 0)) {
            $this->returnForbiddenResponse("SECTION ID WAS NOT PROVIDED OR NOT FORMATTED PROPERLY");
        }

        $section = $this->sectionService->getSectionById($sectionId);
        if (!$section) {
            $this->returnForbiddenResponse("SECTION ".$sectionId." DOES NOT EXIST");
        }
        
        if ($section->course->is_contest) {
            return $this->returnForbiddenResponse("contest_edit", 
            [
                "contestId" => $sectionId
            ]);
        }
        
        $user = $this->userService->getCurrentUser();
        if (!get_class($user)) {
            $this->returnForbiddenResponse("USER DOES NOT EXIST");
        }
        
        // validate the user
        if (!$user->hasRole(CONSTANTS::SUPER_ROLE) && 
            !$user->hasRole(CONSTANTS::ADMIN_ROLE) && 
            !$this->graderService->isTeaching($user, $section) && 
            !$this->graderService->isJudging($user, $section)
            ) {
            $this->returnForbiddenResponse("YOU ARE NOT ALLOWED TO EDIT THIS ASSIGNMENT");
        }		
        
        if ($assignmentId != 0) {
            if (!($assignmentId > 0)) {
                $this->returnForbiddenResponse("ASSIGNMENT ID WAS NOT FORMATTED PROPERLY");
            }
                                    
            $assignment = $this->assignmentService->getAssignmentById($assignmentId);

            if (!$assignment || $section != $assignment->section) {
                $this->returnForbiddenResponse("ASSIGNMENT ".$assignmentId." DOES NOT EXIST OR DOES NOT BELONG TO SECTION ".$sectionId);
            }
        }
                
        // get all the users taking the course
        $sectionTakerRoles = $this->userSectionRoleService->getUserSectionRolesForAssignmentEdit($section);

        $students = [];
        foreach ($sectionTakerRoles as $sectionTakerRole) {
            $user = $sectionTakerRole->user;

            $student = [];
            $student["id"] = $user->id;
            $student["name"] = $user->getFullName();

            $students[] = $student;
        }

        return $this->render("assignment/edit.html.twig", [
            "assignment" => $assignment,
            "section" => $section,
            "edit" => true,
            "students" => $students
        ]);
    }

    public function deleteAction($sectionId, $assignmentId) {
        // get the assignment
        if (!isset($assignmentId) || !($assignmentId > 0)) {
            $this->returnForbiddenResponse("ASSIGNMENT ID WAS NOT PROVIDED OR FORMATTED PROPERLY");
        }
        
        $assignment = $this->assignmentService->getAssignmentById($assignmentId);
        if (!$assignment) {
            $this->returnForbiddenResponse("ASSIGNMENT ".$assignmentId." DOES NOT EXIST");
        }
        
        $user = $this->userService->getCurrentUser();
        if (!$user) {
            $this->returnForbiddenResponse("USER DOES NOT EXIST");
        }
        
        // validate the user
        if (!$user->hasRole(CONSTANTS::SUPER_ROLE) && 
            !$user->hasRole(CONSTANTS::ADMIN_ROLE) && 
            !$this->graderService->isTeaching($user, $assignment->section) && 
            !$this->graderService->isJudging($user, $assignment->section)) {
            $this->returnForbiddenResponse("YOU ARE NOT ALLOWED TO DELETE ASSIGNMENT ".$assignmentId);
        }

        $section = $this->sectionService->getSectionById($sectionId);
        if ($section->is_master) {
            $slaves = $section->slaves;
            foreach ($slaves as $slave) {
                $slaveAssignments = $slave->assignments;
                foreach ($slaveAssignments as $slaveAssignment) {
                    if ($slaveAssignment->name == $assignment->name) {
                        $this->assignmentService->deleteAssignment($slaveAssignment, false);
                    }
                }
            }
        }
        
        $this->assignmentService->deleteAssignment($assignment);
        
        return $this->redirectToRoute("section", 
        [
            "sectionId" => $assignment->section->id
        ]);
    }
    
    public function modifyPostAction(Request $request) {
        // validate the current user
        $user = $this->userService->getCurrentUser();
        if (!$user) {
            return $this->returnForbiddenResponse("YOU ARE NOT A USER");
        }
        
        // see which fields were included
        $postData = $request->request->all();
        
        // get the current section
        // get the assignment
        if (!isset($postData["section"]) || !($postData["section"] > 0)) {
            return $this->returnForbiddenResponse("SECTION ID WAS NOT PROVIDED OR FORMATTED PROPERLY");
        }
        
        $sectionId = $postData["section"];
        
        $section = $this->sectionService->getSectionById($sectionId);
        
        if (!$section) {
            return $this->returnForbiddenResponse("SECTION ".$sectionId." DOES NOT EXIST");
        }
        
        // only super users/admins/teacher can make/edit an assignment
        if (!$user->hasRole(CONSTANTS::SUPER_ROLE) && 
            !$user->hasRole(CONSTANTS::ADMIN_ROLE) && 
            !$this->graderService->isTeaching($user, $section) && 
            !$this->graderService->isJudging($user, $section)) {
            return $this->returnForbiddenResponse("YOU DO NOT HAVE PERMISSION TO MAKE AN ASSIGNMENT");
        }		
        
        // check mandatory fields
        if (!isset($postData["name"]) ||
            !isset($postData["open_time"]) ||
            !isset($postData["close_time"]) ||
            !isset($postData["teams"]) ||
            !isset($postData["teamnames"])) {
                return $this->returnForbiddenResponse("NOT EVERY REQUIRED FIELD WAS PROVIDED");
            }
            // validate the weight if there is one
            if (is_numeric(trim($postData["weight"])) && ((int)trim($postData["weight"]) < 0 || $postData["weight"] % 1 != 0)) {
            return $this->returnForbiddenResponse("THE PROVIDED WEIGHT ".$postData["weight"]." IS NOT PERMITTED");
        }
        
        // validate the penalty if there is one
        if (is_numeric(trim($postData["penalty"])) && ((float)trim($postData["penalty"]) > 1.0 || (float)trim($postData["penalty"]) < 0.0)) {		
            return $this->returnForbiddenResponse("THE PROVIDED PENALTY ".$postData["penalty"]." IS NOT PERMITTED");
        }		
        
        // create new assignment
        $assignment = null;
        $assignmentId = $postData["assignment"];
        if ($postData["assignment"] == 0) {
            $assignment = $this->assignmentService->createEmptyAssignment();
        } else {
            if (!isset($assignmentId) || !($assignmentId > 0)) {
                return $this->returnForbiddenResponse("ASSIGNMENT ID WAS NOT PROVIDED OR FORMATTED PROPERLY");
            }
            
            $assignment = $this->assignmentService->getAssignmentById($assignmentId);
            
            if (!$assignment || $section != $assignment->section) {
                return $this->returnForbiddenResponse("ASSIGNMENT ".$assignmentId." DOES NOT EXIST FOR THE GIVEN SECTION ".$sectionId);
            }			
        }
        
        // set the necessary fields
        $assignment->name = trim($postData["name"]);
        $assignment->description = trim($postData["description"]);
        $assignment->section = $section;
        
        // set the times
        $openTime = DateTime::createFromFormat("m/d/Y H:i:s", $postData["open_time"].":00");
        $closeTime = DateTime::createFromFormat("m/d/Y H:i:s", $postData["close_time"].":00");
        
        if (!isset($openTime) || $openTime->format("m/d/Y H:i") != $postData["open_time"]) {
            return $this->returnForbiddenResponse("PROVIDED OPENING TIME ".$postData["open_time"]." IS NOT VALID");
        }
        
        if (!isset($closeTime) || $closeTime->format("m/d/Y H:i") != $postData["close_time"]) {
            return $this->returnForbiddenResponse("PROVIDED CLOSING TIME ".$postData["close_time"]." IS NOT VALID");
        }
        
        if (isset($postData["cutoff_time"]) && $postData["cutoff_time"] != "") {	
            $cutoffTime = DateTime::createFromFormat("m/d/Y H:i:s", $postData["cutoff_time"].":00");
            
            if (!isset($cutoffTime) || $cutoffTime->format("m/d/Y H:i") != $postData["cutoff_time"]) {
                return $this->returnForbiddenResponse("PROVIDED CUTOFF TIME ".$postData["cutoff_time"]." IS NOT VALID");
            }
        } else {
            $cutoffTime = $closeTime;
        }
        
        if ($cutoffTime < $closeTime || $closeTime < $openTime) {
            return $this->returnForbiddenResponse("PROVIDED TIMES ARE NOT VALID. THE CLOSING TIME MUST BE AFTER THE OPENING TIME.");
        }
        
        $assignment->start_time = $openTime;
        $assignment->end_time = $closeTime;
        $assignment->cutoff_time = $cutoffTime;
        
        // set the weight
        if (isset($postData["weight"])) {
            $assignment->weight = (int)trim($postData["weight"]);
        } else {
            $assignment->weight = 1;
        }				

        // set extra credit
        if (isset($postData["is_extra_credit"]) && $postData["is_extra_credit"] == "true") {
            $assignment->is_extra_credit = true;
        } else {			
            $assignment->is_extra_credit = false;
        }
        
        // set grading penalty
        $penalty = (float)trim($postData["penalty"]);
        $assignment->penalty_per_day = $penalty;
    
        // get all the users taking the course
        $sectionTakerRoles = $this->userSectionRoleService->getUserSectionRolesForAssignmentEdit($section);
        
        $sectionTakers = [];
        foreach($sectionTakerRoles as $sectionTakerRole){
            $sectionTakers[] = $sectionTakerRole->user;
        }

        // build teams
        $teamsJson = json_decode($postData["teams"]);
        $teamNamesJson = json_decode($postData["teamnames"]);
        $teamIdsJson = json_decode($postData["teamids"]);
        
        if (count($teamsJson) != count($teamNamesJson)) {
            return $this->returnForbiddenResponse("THE NUMBER OF TEAMMATES DOES NOT EQUAL THE NUMBER OF TEAMS");
        }

        $oldTeams = $assignment->teams;
        $modTeams = new ArrayCollection();
        
        $count = 0;
                
        foreach ($teamsJson as $teamJson) {
            $teamId = $teamIdsJson[$count];

            // editing a current team
            if ($teamId != 0) {
                $team = $this->teamService->getTeamById($teamId);

                if (!$team || $team->assignment != $assignment) {
                    return $this->returnForbiddenResponse("TEAM ".$teamId." DOES NOT EXIST FOR ASSIGNMENT ".$assignmentId);
                }
                
                $team->name = $teamNamesJson[$count];
                $team->users = new ArrayCollection();
                
                foreach ($teamJson as $userId) {
                    $temp_user = $this->userService->getUserById($userId);

                    if (!$temp_user || !in_array($temp_user, $sectionTakers)) {
                        return $this->returnForbiddenResponse("USER ".$userId." DOES NOT TAKE THIS CLASS");
                    }

                    $team->users[] = $temp_user;										
                }
                
                if (count($team->users) == 0) {
                    return $this->returnForbiddenResponse($team->name." DID NOT HAVE ANY USERS PROVIDED");
                }
                
                $this->teamService->insertTeam($team, false);
                $modTeams->add($team->id);
            } else {
                $team = new Team($teamNamesJson[$count], $assignment);
            
                foreach ($teamJson as $userId) {
                    $temp_user = $this->userService->getUserById($userId);

                    if (!$temp_user || !in_array($temp_user, $sectionTakers)) {
                        return $this->returnForbiddenResponse("USER ".$userId." DOES NOT TAKE THIS CLASS");
                    }

                    $team->users[] = $temp_user;
                }
                
                if (count($team->users) == 0) {
                    return $this->returnForbiddenResponse($team->name." DID NOT HAVE ANY USERS PROVIDED");
                }
                
                $this->teamService->insertTeam($team, false);
            }
            
            $count++;
        }
        
        // remove the old teams that no longer exist
        foreach ($oldTeams as $oldTeam){
            if (!$modTeams->contains($oldTeam->id)) {
                $this->teamService->deleteTeam($oldTeam, false);
            }
        }

        $this->assignmentService->insertAssignment($assignment);
        
        $url = $this->generateUrl("assignment", 
        [
            "sectionId" => $assignment->section->id, 
            "assignmentId" => $assignment->id
        ]);
                
        $response = new Response(json_encode([
            "redirect_url" => $url, 
            "assignment" => $assignment
        ]));
        
        return $this->returnOkResponse($response);
    }
    
    public function clearSubmissionsAction(Request $request) {
        // validate the current user
        $user = $this->userService->getCurrentUser();
        if (!$user) {
            return $this->returnForbiddenResponse("YOU ARE NOT A USER");
        }
        
        // see which fields were included
        $postData = $request->request->all();
        
        // get the current section
        // get the assignment
        $assignmentId = $postData["assignment"];
        if (!isset($assignmentId)) {
            return $this->returnForbiddenResponse("ASSIGNMENT ID WAS NOT PROVIDED");
        }
        
        $assignment = $this->assignmentService->getAssignmentById($assignmentId);
        if (!$assignment) {
            return $this->returnForbiddenResponse("ASSIGNMENT ".$assignmentId." DOES NOT EXIST");
        }

        $section = $assignment->section;
        
        // only super users/admins/teacher can make/edit an assignment
        if (!$user->hasRole(CONSTANTS::SUPER_ROLE) &&
            !$user->hasRole(CONSTANTS::ADMIN_ROLE) && 
            !$this->graderService->isTeaching($user, $section) && 
            !$this->graderService->isJudging($user, $section)) {
            return $this->returnForbiddenResponse("YOU DO NOT HAVE PERMISSION TO DO THIS");
        }

        // delete all submission but keep all of the trials
        $result = $this->submissionService->deleteAllSubmissionsForAssignmentClearSubmissions($assignment->problems->toArray());
        $response = new Response(json_encode([
            "result" => $result,
        ]));

        return $this->returnOkResponse($response);	
    }

    public function clearTrialsAction(Request $request) {
        // validate the current user
        $user = $this->userService->getCurrentUser();
        if (!$user) {
            return $this->returnForbiddenResponse("YOU ARE NOT A USER");
        }
        
        // see which fields were included
        $postData = $request->request->all();
        
        // get the current section
        // get the assignment
        $assignmentId = $postData["assignment"];
        if (!isset($assignmentId)) {
            return $this->returnForbiddenResponse("ASSIGNMENT ID WAS NOT PROVIDED");
        }
        
        $assignment = $this->assignmentService->getAssignmentById($assignmentId);
        if (!$assignment) {
            return $this->returnForbiddenResponse("ASSIGNMENT ".$assignmentId." DOES NOT EXIST");
        }

        $section = $assignment->section;
        
        // only super users/admins/teacher can make/edit an assignment
        if (!$user->hasRole(CONSTANTS::SUPER_ROLE) && 
            !$user->hasRole(CONSTANTS::ADMIN_ROLE) &&
            !$this->graderService->isTeaching($user, $section) && 
            !$this->graderService->isJudging($user, $section)) {			
            return $this->returnForbiddenResponse("YOU DO NOT HAVE PERMISSION TO DO THIS");
        }

        // delete all submissions but keep all of the trials
        $result = $this->submissionService->deleteAllSubmissionsForAssignmentClearSubmissions($assignment->problems->toArray());
        $response = new Response(json_encode([
            "result" => $result,
        ]));
        
        return $this->returnOkResponse($response);		
    }
    
    private function logError($message) {
        $errorMessage = "AssignmentController: ".$message;
        $this->logger->error($errorMessage);
        return $errorMessage;
    }
    
    private function returnForbiddenResponse($message){		
        $response = new Response($message);
        $response->setStatusCode(Response::HTTP_FORBIDDEN);
        $this->logError($message);
        return $response;
    }

    private function returnOkResponse($response) {
        $response->headers->set("Content-Type", "application/json");
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }
}

?>
