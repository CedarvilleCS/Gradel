<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use AppBundle\Constants;

use AppBundle\Entity\Submission;
use AppBundle\Entity\Problem;
use AppBundle\Entity\ProblemLanguage;
use AppBundle\Entity\Language;
use AppBundle\Entity\UserSectionRole;
use AppBundle\Entity\Testcase;

use AppBundle\Service\AssignmentService;
use AppBundle\Service\GraderService;
use AppBundle\Service\LanguageService;
use AppBundle\Service\ProblemLanguageService;
use AppBundle\Service\ProblemService;
use AppBundle\Service\SectionService;
use AppBundle\Service\SubmissionService;
use AppBundle\Service\TeamService;
use AppBundle\Service\TestCaseService;
use AppBundle\Service\TrialService;
use AppBundle\Service\UserSectionRoleService;
use AppBundle\Service\UserService;

use AppBundle\Utils\Grader;
use AppBundle\Utils\Zipper;

use Psr\Log\LoggerInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Config\Definition\Exception\Exception;

class ProblemController extends Controller {
    private $assignmentService;
    private $graderService;
    private $languageService;
    private $logger;
    private $problemLanguageService;
    private $problemService;
    private $sectionService;
    private $submissionService;
    private $teamService;
    private $testCaseService;
    private $trialService;
    private $userSectionRoleService;
    private $userService;

    public function __construct(AssignmentService $assignmentService,
                                GraderService $graderService,
                                LoggerInterface $logger,
                                LanguageService $languageService,
                                ProblemLanguageService $problemLanguageService,
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
        $this->languageService = $languageService;
        $this->logger = $logger;
        $this->problemLanguageService = $problemLanguageService;
        $this->problemService = $problemService;
        $this->sectionService = $sectionService;
        $this->submissionService = $submissionService;
        $this->teamService = $teamService;
        $this->testCaseService = $testCaseService;
        $this->trialService = $trialService;
        $this->userSectionRoleService = $userSectionRoleService;
        $this->userService = $userService;
    }

    public function problemAction($sectionId, $assignmentId, $problemId) {
        $user = $this->userService->getCurrentUser();
        $requestingUser = $this->getUser();
        if (!get_class($user)) {
            return $this->returnForbiddenResponse("USER DOES NOT EXIST");
        }
        
        /* Get the section */
        if (!isset($sectionId) || !($sectionId > 0)){
            return $this->returnForbiddenResponse("SECTION ID WAS NOT PROVIDED OR FORMATTED PROPERLY");
        }
        
        $section = $this->sectionService->getSectionById($sectionId);
        if (!$section) {
            return $this->returnForbiddenResponse("SECTION ".$sectionId." DOES NOT EXIST");
        }
        
        /* REDIRECT TO CONTEST IF NEED BE */
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
        
        /* Get the assignment */
        if (!isset($assignmentId) || !($assignmentId > 0)) {
            return $this->returnForbiddenResponse("ASSIGNMENT ID WAS NOT PROVIDED OR FORMATTED PROPERLY");
        }
        
        $assignment = $this->assignmentService->getAssignmentById($assignmentId);
        if (!$assignment) {
            return $this->returnForbiddenResponse("ASSIGNMENT ".$assignmentId." DOES NOT EXIST");
        }
        
        $languages = [];
        $aceModes = [];
        $fileTypes = [];
        if (!($problemId > 0)) {
            return $this->returnForbiddenResponse("PROBLEM ID WAS NOT FORMATTED PROPERLY");
        }
        
        $problem = $this->problemService->getProblemById($problemId);
        if (!$problem || $problem->assignment != $assignment) {
            return $this->returnForbiddenResponse("PROBLEM ".$problemId." DOES NOT EXIST");
        }
        
        $problemLanguages = $problem->problem_languages;
        foreach ($problemLanguages as $problemLanguage) {
            $languages[] = $problemLanguage->language;
            $aceModes[$problemLanguage->language->name] = $problemLanguage->language->ace_mode;
            $fileTypes[str_replace(".", "", $problemLanguage->language->filetype)] = $problemLanguage->language->name;
        }

        // get the usersectionrole
        $userSectionRole = $this->userSectionRoleService->getUserSectionRolesForAssignment($requestingUser, $problem->assignment->section);
        
        // figure out how many attempts they have left
        $totalAttempts = $problem->total_attempts;
        $attemptsRemaining = -1;
        if ($totalAttempts != 0 && 
            !$this->graderService->isTeaching($user, $assignment->section) && 
            !$this->graderService->isJudging($user, $assignment->section)) {
            $attemptsRemaining = max($totalAttempts - $this->graderService->getNumTotalAttempts($user, $problem), 0);
        }
        
        // get the team
        $team = $this->teamService->getTeam($user, $assignment);

        $teamOrUser = $user;
        $whereClause = "s.user = ?1";
        if (isset($team)) {
            $teamOrUser = $team;
            $whereClause = "s.team = ?1";
        }

        // get the best submission so far
        $bestSubmission = $this->submissionService->getBestSubmissionForAssignment($teamOrUser, $whereClause, $problem);
        // get the code from the last submissions
        $allSubmissions = $this->submissionService->getAllSubmissionsForAssignment($teamOrUser, $whereClause, $problem);
        
        // get the user's trial for this problem
        $trial = $this->trialService->getTrialForAssignment($user, $problem);
        
        $submissionId = $_GET["submissionId"];
        if (isset($submissionId) && $submissionId > 0) {
            $submission = $this->submissionService->getSubmissionById($submissionId);
            
            if ($submission->user != $user || $submission->problem != $problem) {
                return $this->returnForbiddenResponse($user->name." is not allowed to edit this submission on this problem");
            }
                        
            if (!$trial) {
                $trial = $this->trialService->createTrial($user, $problem);
                $this->trialService->insertTrial($trial);
            }
            
            $trial->file = $submission->submitted_file;						
            
            $trial->language = $submission->language;	
            $trial->filename = $submission->filename;
            $trial->main_class = $submission->main_class_name;
            $trial->package_name = $submission->package_name;
            $trial->last_edit_time = new \DateTime("now");
            
            return $this->redirectToRoute("assignment", 
            [
                "sectionId" => $section->id, 
                "assignmentId" => $assignment->id, 
                "problemId" => $problem->id
            ]);
        }
        
        // get all userSectionRoles
        $userSectionRoles = $this->userSectionRoleService->getUserSectionRolesOfSection($section);

        $sectionTakers = [];
        $sectionTeachers = [];
        $sectionHelpers = [];
        $sectionJudges = [];

        foreach ($userSectionRoles as $usr) {
            $user = $usr->user;
            $roleName = $usr->role->role_name;
            switch ($roleName) {
                case Constants::HELPS_ROLE:
                    $sectionHelpers[] = $usr->user;
                    break;
                case Constants::JUDGES_ROLE:
                    $sectionJudges[] = $usr->user;
                    break;
                case Constants::TAKES_ROLE:
                    $sectionTakers[] = $usr->user;
                    break;
                case Constants::TEACHES_ROLE:
                    $sectionTeachers[] = $usr->user;
                    break;
                default:
                    return $this->returnForbiddenResponse("ROLE ".$roleName." DOES NOT EXIST ON USER ".$user->getFullName());
            }
        }
        
        return $this->render("problem/index.html.twig", [
            "ace_modes" => $aceModes,
            "all_submissions" => $allSubmissions,
            "assignment" => $assignment,
            "attempts_remaining" => $attemptsRemaining,
            "best_submission" => $bestSubmission,
            "filetypes" => $fileTypes,
            "grader" => $this->graderService,
            "grades" => null,
            "languages" => $languages,
            "problem" => $problem,
            "section" => $assignment->section,
            "section_takers" => $sectionTakers,
            "team" => $team,
            "trial" => $trial,
            "user" => $user,
            "user_impersonators" => $sectionTakers,
            "usersectionrole" => $userSectionRole
        ]);
    }

     public function editAction($sectionId, $assignmentId, $problemId) {
        /* Validate the user */
        $user = $this->userService->getCurrentUser();
        if (!$user) {
            return $this->returnForbiddenResponse("USER DOES NOT EXIST");
        }

        $languages = $this->languageService->getAll();
        
        if (!isset($sectionId) || !($sectionId > 0)) {
            return $this->returnForbiddenResponse("SECTION ID WAS NOT PROVIDED OR NOT FORMATTED PROPERLY");
        }
        
        $section = $this->sectionService->getSectionById($sectionId);
        if (!$section) {
            return $this->returnForbiddenResponse("SECTION ".$sectionId." DOES NOT EXIST");
        }
        
        /* Redirect to contest_problem_edit if need be */
        if ($section->course->is_contest) {
            return $this->redirectToRoute("contest_problem_edit", [
                "contestId" => $sectionId, 
                "roundId" => $assignmentId, 
                "problemId" => $problemId
            ]);
        }
        
        if (!isset($assignmentId) || !($assignmentId > 0)) {
            return $this->returnForbiddenResponse("ASSIGNMENT ID WAS NOT PROVIDED OR NOT FORMATTED PROPERLY");
        }
        
        $assignment = $this->assignmentService->getAssignmentById($assignmentId);

        if (!$assignment) {
            return $this->returnForbiddenResponse("ASSIGNMENT ".$assignmentId." DOES NOT EXIST");
        }
        
        if ($problemId != 0) {
            if (!isset($problemId) || !($problemId > 0)) {
                return $this->returnForbiddenResponse("PROBLEM ID WAS NOT PROVIDED OR NOT FORMATTED PROPERLY");
            }		
            
            $problem = $this->problemService->getProblemById($problemId);
            
            if (!$problem) {
                return $this->returnForbiddenResponse("PROBLEM ".$problemId." DOES NOT EXIST");
            }			
                        
            if ($problem->master) {
                $problem = $problem->master;
                
                return $this->redirectToRoute("problem_edit", [
                    "sectionId" => $problem->assignment->section->id, 
                    "assignmentId" => $problem->assignment->id, 
                    "problemId" => $problem->id
                ]);
            }
        }
        
        $aceModes = [];
        $filetypes = [];
        foreach ($languages as $language) {
            $aceModes[$language->name] = $language->ace_mode;
            $filetypes[str_replace(".", "", $language->filetype)] = $language->name;
        }
        
        $recommendedSlaves = [];
        if ($problem) {
            $recommendedSlaves = $this->problemService->getProblemsByObject(["name" => $problem->name]);
        }

        return $this->render("problem/edit.html.twig", [
            "ace_modes" => $aceModes,
            "assignment" => $assignment,
            "edit_route" => true, 
            "filetypes" => $filetypes,
            "languages" => $languages,
            "problem" => $problem,	
            "recommendedSlaves" => $recommendedSlaves,
            "section" => $section
        ]);
    }

    public function deleteAction($sectionId, $assignmentId, $problemId) {
        /* Validate the user */
        $user = $this->userService->getCurrentUser();
        if (!$user) {
            return $this->returnForbiddenResponse("USER DOES NOT EXIST");
        }

        if (!isset($problemId) || !($problemId > 0)) {
            return $this->returnForbiddenResponse("PROBLEM ID WAS NOT PROVIDED OR NOT FORMATTED PROPERLY");
        }

        $problem = $this->problemService->getProblemById($problemId);
        if (!$problem) {
            return $this->returnForbiddenResponse("PROBLEM ".$problemId." DOES NOT EXIST");
        }

        if (!$user->hasRole(Constants::SUPER_ROLE) && !$user->hasRole(Constants::ADMIN_ROLE) && !$this->graderService->isTeaching($user, $problem->assignment->section)) {
            return $this->returnForbiddenResponse("YOU ARE NOT ALLOWED TO DELETE THIS PROBLEM");
        }

        $this->problemService->deleteProblem($problem);
        return $this->redirectToRoute("assignment", [
            "sectionId" => $problem->assignment->section->id, 
            "assignmentId" => $problem->assignment->id
        ]);
    }

    public function modifyPostAction(Request $request) {
        /* Validate the user */
        $user = $this->userService->getCurrentUser();
        if (!$user) {
            return $this->returnForbiddenResponse("USER DOES NOT EXIST");
        }

        $postData = $request->request->all();

        /* Get the current assignment */
        $assignmentId = $postData["assignmentId"];
        if (!isset($assignmentId) || !($assignmentId > 0)) {
            return $this->returnForbiddenResponse("ASSIGNMENT ID WAS NOT PROVIDED OR NOT FORMATTED PROPERLY");
        }
        
        $assignment = $this->assignmentService->getAssignmentById($assignmentId);
        if (!$assignment) {
            return $this->returnForbiddenResponse("ASSIGNMENT ".$assignmentId." DOES NOT EXIST");
        }

        /* Only super users/admins/teacher can make/edit an assignment */
        if (!$user->hasRole(Constants::SUPER_ROLE) && 
            !$user->hasRole(Constants::ADMIN_ROLE) && 
            !$this->graderService->isTeaching($user, $assignment->section)) {
            return $this->returnForbiddenResponse("YOU DO NOT HAVE PERMISSION TO MAKE A PROBLEM");
        }
        
        /* Get the problem or create a new one */
        $problemId = $postData["problem"];
        if ($problemId == 0) {
            $problem = $this->problemService->createEmptyProblem();
            $problem->assignment = $assignment;
            $this->problemService->insertProblem($problem, false);
        } else {
            if (!isset($problemId) || !($problemId > 0)) {
                return $this->returnForbiddenResponse("PROBLEM ID WAS NOT PROVIDED OR NOT FORMATTED PROPERLY");
            }

            $problem = $this->problemService->getProblemById($problemId);

            if (!$problem || $assignment != $problem->assignment) {
                return $this->returnForbiddenResponse("PROBLEM ".$problemId." DOES NOT EXIST FOR THE GIVEN ASSIGNMENT");
            }
        }

        /* Check mandatory fields */
        $problemName = $postData["name"];
        $problemDescription = $postData["description"];
        $problemWeight = $postData["weight"];
        $problemTimeLimit = $postData["time_limit"];
        if (!isset($problemName) || 
            trim($problemName) == "" || 
            !isset($problemDescription) || 
            trim($problemDescription) == "" || 
            !isset($problemWeight) || 
            !isset($problemTimeLimit)) {
            return $this->returnForbiddenResponse("NOT EVERY NECESSARY FIELD WAS PROVIDED");
        } else {
            if (!is_numeric(trim($problemWeight)) || (int)trim($problemWeight) < 0) {
                return $this->returnForbiddenResponse("WEIGHT PROVIDED IS NOT VALID - IT MUST BE NON-NEGATIVE");
            }

            if (!is_numeric(trim($problemTimeLimit)) || (int)trim($problemTimeLimit) < 1) {
                return $this->returnForbiddenResponse("TIME LIMIT PROVIDED IS NOT VALID - IT MUST BE NON-NEGATIVE");
            }
        }

        $problem->version = $problem->version + 1;
        $problem->name = trim($problemName);
        $problem->description = trim($problemDescription);
        $problem->weight = (int)trim($problemWeight);
        $problem->is_extra_credit = ($postData["is_extra_credit"] == "true");
        $problem->time_limit = (int)trim($problemTimeLimit);
        
        $problemLanguages = $postData["languages"];
        $problemTestCases = $postData["testcases"];
        if (!isset($problemLanguages)) {
            return $this->returnForbiddenResponse("LANGUAGES WERE NOT PROVIDED");
        }
        if (!isset($problemTestCases)) {
            return $this->returnForbiddenResponse("TESTCASES WERE NOT PROVIDED");
        }
        if (count($problemLanguages) < 1) {
            return $this->returnForbiddenResponse("YOU MUST SPECIFY AT LEAST ONE LANGUAGE");
        }
        if (count($problemTestCases) < 1){
            return $this->returnForbiddenResponse("YOU MUST SPECIFY AT LEAST ONE TEST CASE");
        }

        /* Check the optional fields */
        /* Attempt penalties */
        $totalAttempts = $postData["total_attempts"];
        $attemptsBeforePenalty = $postData["attempts_before_penalty"];
        $penaltyPerAttempt = $postData["penalty_per_attempt"];

        if (!isset($totalAttempts) || 
            !is_numeric($totalAttempts) || 
            !isset($attemptsBeforePenalty) || 
            !is_numeric($attemptsBeforePenalty) || 
            !isset($penaltyPerAttempt) || 
            !is_numeric($penaltyPerAttempt)) {
            return $this->returnForbiddenResponse("NOT EVERY NECESSARY GRADING FLAG WAS SET PROPERLY");
        }

        if ($totalAttempts < $attemptsBeforePenalty) {
            return $this->returnForbiddenResponse("ATTEMPTS BEFORE PENALTY MUST BE GREATER THAN TOTAL ATTEMPTS");
        }

        if ($penaltyPerAttempt < 0.00 || $penaltyPerAttempt > 1.00) {
            return $this->returnForbiddenResponse("PENALTY PER ATTEMPT MUST BE BETWEEN 0 AND 1");
        }

        $problem->total_attempts = $totalAttempts;
        $problem->attempts_before_penalty = $attemptsBeforePenalty;
        $problem->penalty_per_attempt = $penaltyPerAttempt;

        # feedback flags
        $stopOnFirstFail = $postData["stop_on_first_fail"];
        $responseLevel = trim($postData["response_level"]);
        $displayTestCaseResults = $postData["display_testcaseresults"];
        $testcaseOutputLevel = trim($postData["testcase_output_level"]);
        $extraTestcasesDisplay = $postData["extra_testcases_display"];

        if ($stopOnFirstFail != null || 
            $responseLevel != null || 
            $displayTestCaseResults != null || 
            $testcaseOutputLevel != null || 
            $extraTestcasesDisplay != null) {
            if ($stopOnFirstFail == null || 
                $responseLevel == null || 
                $displayTestCaseResults == null || 
                $testcaseOutputLevel == null || 
                $extraTestcasesDisplay == null) {
                return $this->returnForbiddenResponse("NOT EVERY NECESSARY FEEDBACK FLAG WAS SET");
            }

            if ($responseLevel != Constants::LONG_RESPONSE_LEVEL && 
                $responseLevel != Constants::SHORT_RESPONSE_LEVEL && 
                $responseLevel != Constants::NONE_RESPONSE_LEVEL) {
                return $this->returnForbiddenResponse("RESPONSE VALUE WAS NOT VALID");
            }

            if ($testcaseOutputLevel != Constants::BOTH_TESTCASE_OUTPUT_LEVEL && 
                $testcaseOutputLevel != Constants::OUTPUT_TESTCASE_OUTPUT_LEVEL && 
                $testcaseOutputLevel != Constants::NONE_TESTCASE_OUTPUT_LEVEL) {
                return $this->returnForbiddenResponse("TESTCASE OUTPUT VALUE WAS NOT VALID");
            }
        } else {
            $displayTestCaseResults = true;
            $extraTestcasesDisplay = true;
            $responseLevel = Constants::LONG_RESPONSE_LEVEL;
            $stopOnFirstFail = false;
            $testcaseOutputLevel = Constants::BOTH_TESTCASE_OUTPUT_LEVEL;
        }

        $problem->stop_on_first_fail = ($stopOnFirstFail == "true");
        $problem->response_level = $responseLevel;
        $problem->display_testcaseresults = ($displayTestCaseResults == "true");
        $problem->testcase_output_level = $testcaseOutputLevel;
        $problem->extra_testcases_display = ($extraTestcasesDisplay == "true");	
        
        /* Allow adding files (tabs) */
        $allow_multiple = $postData["allow_multiple"];
        $problem->allow_multiple = ($allow_multiple == "true");

        /* Allow uploading files */
        $allow_upload = $postData["allow_upload"];
        $problem->allow_upload = ($allow_upload == "true");
        
        /* Linked problems */
        if (!$problem->assignment->section->course->is_contest) {
            foreach ($problem->slaves as &$slave) {
                $slave->master = null;
            }
            
            $decodedProblemLinks = json_decode($postData["linked_probs"]);
            foreach ($decodedProblemLinks as $decodedProblemLink) {
                $linkedProblem = $this->problemService->getProblemById($decodedProblemLink);

                if (!$linkedProblem) {
                    return $this->returnForbiddenResponse("PROBLEM ".$decodedProblemLink." DOES NOT EXIST");
                }
                
                $problem->slaves->add($linkedProblem);
                $linkedProblem->master = $problem;
            }
        }		
        
        /* Custom validator */
        $customValidator = trim($postData["custom_validator"]);
        if (isset($customValidator) && $customValidator != "") {
            $problem->custom_validator = $customValidator;
        } else {
            $problem->custom_validator = null;
        }
        
        /* Go through the problemlanguages */
        /* Remove the old ones */
        $oldDefaultCode = [];

        foreach ($problem->problem_languages as $oldProblemLanguage) {
            $oldDefaultCode[$oldProblemLanguage->language->id] = $oldProblemLanguage->default_code;
            $this->problemLanguageService->deleteProblemLanguage($oldProblemLanguage);
        }

        $newProblemLanguages = [];
        $decodedLanguages = json_decode($postData["languages"]);
        foreach ($decodedLanguages as $decodedLanguage) {
            if (!isset($decodedLanguage->id) || !($decodedLanguage->id > 0)) {
                return $this->returnForbiddenResponse("YOU DID NOT SPECIFY A LANGUAGE ID");
            }

            $language = $this->languageService->getLanguageById($decodedLanguage->id);
            if (!$language) {
                return $this->returnForbiddenResponse("LANGUAGE ".$decodedLanguage->id." DOES NOT EXIST");
            }

            $problemLanguage = $this->problemLanguageService->createProblemLanguage($problem, $language);			
            /* Set compiler options and default code */
            if (isset($decodedLanguage->compiler_options) && strlen($decodedLanguage->compiler_options) > 0) {
                /* Check the compiler options for invalid characters */
                if (preg_match("/^[ A-Za-z0-9+=\-]+$/", $decodedLanguage->compiler_options) != 1) {
                    return $this->returnForbiddenResponse("THE COMPILER OPTIONS PROVIDED HAVE INVALID CHARACTERS");
                }
            
                $problemLanguage->compilation_options = $decodedLanguage->compiler_options;
            }
            
            if (isset($decodedLanguage->default_code) && strlen($decodedLanguage->default_code) > 0) {
                $problemLanguage->default_code = $decodedLanguage->default_code;
            }

            /* Get the contents of the default code and save it to a file so we can save */
            $temp = tmpfile();
            $tempFilename = stream_get_meta_data($temp)["uri"];
            $tempLanguageName = $_FILES["file_".$decodedLanguage->id]["tmp_name"];

            if ($tempLanguageName == null) {
                $problemLanguage->default_code = $oldDefaultCode[$decodedLanguage->id];
            } else if (move_uploaded_file($tempLanguageName, $tempFilename)) {
                $fileHandle = fopen($tempFilename, "r");

                if (!$fileHandle) {
                    return $this->returnForbiddenResponse("CANNOT OPEN FILE");
                }

                $problemLanguage->default_code = $fileHandle;
            } else {
                return $this->returnForbiddenResponse("ERROR SAVING DEFAULT CODE");
            }

            $newProblemLanguages[] = $problemLanguage;
            $this->problemLanguageService->insertProblemLanguage($problemLanguage);
        }

        /* Testcases */
        /* Set the old testcases to null */
        /* (so they don"t go away and can be accessed in the results page) */
        foreach ($problem->testcases as &$testCase) {
            $testCase->problem = null;
            $this->testCaseService->insertTestCase($testCase);
        }
        
        $newTestCases = new ArrayCollection();
        $count = 1;

        $decodedTestCases = json_decode($postData["testcases"]);
        foreach ($decodedTestCases as &$decodedTestCase) {
            $decodedTestCase = (array) $decodedTestCase;
            
            /* Build the testcase */
            try {
                $testCase = new Testcase($problem, $decodedTestCase, $count);
                $this->testCaseService->insertTestCase($testCase);
                $newTestCases->add($testCase);
                $count++;
                
            } catch (Exception $e) {
                return $this->returnForbiddenResponse($e->getMessage());
            }
        }

        $problem->testcases = $newTestCases;
        $problem->testcase_counts[] = count($problem->testcases);
        
        /* CONTEST SETTINGS OVERRIDE */
        if ($problem->assignment->section->course->is_contest) {
            $problem->slaves = new ArrayCollection();
            $problem->master = null;
            
            $problem->attempts_before_penalty = 0;
            $problem->display_testcaseresults = false;
            $problem->extra_testcases_display = false;			
            $problem->is_extra_credit = false;
            $problem->penalty_per_attempt = 0;
            $problem->response_level = Constants::NONE_RESPONSE_LEVEL;
            $problem->stop_on_first_fail = true;
            $problem->testcase_output_level = Constants::NONE_TESTCASE_OUTPUT_LEVEL;
            $problem->total_attempts = 0;
            $problem->weight = 1;
        }		
        
        /* Update all the linked problems */
        foreach ($problem->slaves as &$slave) {
            /* Update the version */
            $slave->version = $slave->version + 1;
            
            /* Update the name */
            $slave->name = $problem->name;
            
            /* Update the description */
            $slave->description = $problem->description;
            
            /* Update the languages */
            foreach ($slave->problem_languages as &$slaveProblemLanguage) {
                $this->problemLanguageService->deleteProblemLanguage($slaveProblemLanguage);
            }
            
            $problemLanguagesClone = new ArrayCollection();
            foreach ($newProblemLanguages as $slaveProblemLanguage) {
                $problemLanguageClone = clone $slaveProblemLanguage;
                $problemLanguageClone->problem = $slave;
                
                $problemLanguagesClone->add($problemLanguageClone);
            }
            $slave->problem_languages = $problemLanguagesClone;
            
            /* Update the weight */
            $slave->weight = $problem->weight;
            
            /* Update extra credit */
            $slave->is_extra_credit = $problem->is_extra_credit;
            
            /* Update the time limit */
            $slave->time_limit = $problem->time_limit;
            
            /* Update the grading options */
            $slave->attempts_before_penalty = $problem->attempts_before_penalty;
            $slave->penalty_per_attempt = $problem->penalty_per_attempt;
            $slave->total_attempts = $problem->total_attempts;
            
            /* Update the submission feedback options */
            $slave->display_testcaseresults = $problem->display_testcaseresults;
            $slave->extra_testcases_display = $problem->extra_testcases_display;
            $slave->response_level = $problem->response_level;
            $slave->stop_on_first_fail = $problem->stop_on_first_fail;
            $slave->testcase_output_level = $problem->testcase_output_level;
            
            /* Update the validator */
            $slave->custom_validator = $problem->custom_validator;
            
            /* Update the test cases */
            foreach ($slave->testcases as &$decodedTestCase) {
                $decodedTestCase->problem = null;
                $this->testCaseService->insertTestCase($decodedTestCase);
            }

            $testcaseClone = new ArrayCollection();	
            foreach ($newTestCases->toArray() as $decodedTestCase) {
                $testCaseClone = clone $decodedTestCase;
                $testCaseClone->problem = $slave;

                $testcaseClone->add($testCaseClone);
            }
            $slave->testcases = $testcaseClone;
            $slave->testcase_counts[] = count($slave->testcases);

            $this->problemService->insertProblem($slave);
        }

        $url = $this->generateUrl("assignment", [
            "sectionId" => $problem->assignment->section->id, 
            "assignmentId" => $problem->assignment->id, 
            "problemId" => $problem->id
        ]);
        
        return new JsonResponse([
            "problemId"=> $problem->id, 
            "redirect_url" => $url
        ]);
    }

    public function resultAction($submissionId) {
        /* Validate the user */
        $user = $this->userService->getCurrentUser();
        if (!$user) {
            return $this->returnForbiddenResponse("USER DOES NOT EXIST!");
        }
        
        if (!isset($submissionId) || !($submissionId > 0)) {
            return $this->returnForbiddenResponse("SUBMISSION ID WAS NOT PROVIDED OR NOT FORMATTED PROPERLY");
        }

        $submission = $this->submissionService->getSubmissionById($submissionId);

        if (!$submission) {
            return $this->returnForbiddenResponse("SUBMISSION ".$submissionId." DOES NOT EXIST");
        }
        
        /* REDIRECT TO CONTEST IF NEED BE */
        if ($submission->problem->assignment->section->course->is_contest) {
            return $this->redirectToRoute("contest_result", [
                "contestId" => $submission->problem->assignment->section->id, 
                "roundId" => $submission->problem->assignment->id, 
                "problemId" => $submission->problem->id, 
                "resultId" => $submission->id
            ]);
        }

        /* Make sure the user has permissions to view the submission result */
        if (!$user->hasRole(Constants::SUPER_ROLE) && 
            !$user->hasRole(Constants::ADMIN_ROLE) && 
            !$this->graderService->isTeaching($user, $submission->problem->assignment->section) && !$this->graderService->isOnTeam($user, $submission->problem->assignment, $submission->team)) {
            return $this->returnForbiddenResponse("YOU ARE NOT ALLOWED TO VIEW THIS SUBMISSION");
        }

        $feedback = $this->graderService->getFeedback($submission);
                
        $aceMode = $submission->language->ace_mode;

        $userSectionRoles = $this->userSectionRoleService->getUserSectionRolesOfSection($submission->problem->assignment->section);

        $sectionTakers = [];

        foreach ($userSectionRoles as $userSectionRole) {
            if ($userSectionRole->role->role_name == Constants::TAKES_ROLE) {
                $sectionTakers[] = $userSectionRole->user;
            }
        }
                
        return $this->render("problem/result.html.twig", [
            "ace_mode" => $aceMode,
            "assignment" => $submission->problem->assignment,
            "feedback" => $feedback,
            "grader" => $this->graderService,
            "problem" => $submission->problem,
            "result_page" => true,
            "result_route" => true, 
            "section" => $submission->problem->assignment->section,
            "submission" => $submission,
            "user_impersonators" => $sectionTakers
        ]);
    }
    
    public function resultDeleteAction($submissionId) {		
        /* Validate the user */
        $user = $this->userService->getCurrentUser();
        if (!get_class($user)) {
            return $this->returnForbiddenResponse("USER DOES NOT EXIST!");
        }

        if (!isset($submissionId) || !($submissionId > 0)) {
            return $this->returnForbiddenResponse("SUBMISSION ID WAS NOT PROVIDED OR NOT FORMATTED PROPERLY");
        }

        $submission = $this->submissionService->getSubmissionById($submissionId);
        if (!$submission) {
            return $this->returnForbiddenResponse("SUBMISSION ".$submissionId." DOES NOT EXIST");
        }

        /* Make sure the user has permissions to view the submission result */
        if (!$user->hasRole(Constants::SUPER_ROLE) && !$this->graderService->isTeaching($user, $submission->problem->assignment->section)) {
            return $this->returnForbiddenResponse("YOU ARE NOT ALLOWED TO DELETE THIS SUBMISSION");
        }
        
        $this->submissionService->deleteSubmission($submission);
        
        return $this->redirectToRoute("assignment", [
            "problemId" => $submission->problem->id, 
            "assignmentId" => $submission->problem->assignment->id, 
            "sectionId" => $submission->problem->assignment->section->id
        ]);	
    }
    
    private function logError($message) {
        $errorMessage = "ProblemController: ".$message;
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