<?php

namespace AppBundle\Controller;

use \DateTime;
use \DateInterval;

use AppBundle\Constants;

use AppBundle\Entity\Assignment;
use AppBundle\Entity\Course;
use AppBundle\Entity\Problem;
use AppBundle\Entity\ProblemLanguage;
use AppBundle\Entity\Query;
use AppBundle\Entity\Role;
use AppBundle\Entity\Section;
use AppBundle\Entity\Submission;
use AppBundle\Entity\Team;
use AppBundle\Entity\Testcase;
use AppBundle\Entity\User;
use AppBundle\Entity\UserSectionRole;

use AppBundle\Service\AssignmentService;
use AppBundle\Service\ContestService;
use AppBundle\Service\CourseService;
use AppBundle\Service\GraderService;
use AppBundle\Service\LanguageService;
use AppBundle\Service\ProblemLanguageService;
use AppBundle\Service\ProblemService;
use AppBundle\Service\QueryService;
use AppBundle\Service\RoleService;
use AppBundle\Service\SectionService;
use AppBundle\Service\SubmissionService;
use AppBundle\Service\TeamService;
use AppBundle\Service\TestCaseService;
use AppBundle\Service\UserSectionRoleService;
use AppBundle\Service\UserService;

use AppBundle\Utils\Grader;
use AppBundle\Utils\SocketPusher;

use Doctrine\Common\Collections\ArrayCollection;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Psr\Log\LoggerInterface;

class ContestPostController extends Controller {
    private $assignmentService;
    private $contestService;
    private $courseService;
    private $graderService;
    private $languageService;
    private $logger;
    private $problemLanguageService;
    private $problemService;
    private $queryService;
    private $roleService;
    private $sectionService;
    private $submissionService;
    private $teamService;
    private $testCaseService;
    private $userSectionRoleService;
    private $userService;

    public function __construct(AssignmentService $assignmentService,
                                ContestService $contestService,
                                CourseService $courseService,
                                GraderService $graderService,
                                LanguageService $languageService,
                                LoggerInterface $logger,
                                ProblemLanguageService $problemLanguageService,
                                ProblemService $problemService,
                                QueryService $queryService,
                                RoleService $roleService,
                                SectionService $sectionService,
                                SubmissionService $submissionService,
                                TeamService $teamService,
                                TestCaseService $testCaseService,
                                UserSectionRoleService $userSectionRoleService,
                                UserService $userService) {
        $this->assignmentService = $assignmentService;
        $this->contestService = $contestService;
        $this->courseService = $courseService;
        $this->graderService = $graderService;
        $this->languageService = $languageService;
        $this->logger = $logger;
        $this->problemLanguageService = $problemLanguageService;
        $this->problemService = $problemService;
        $this->queryService = $queryService;
        $this->roleService = $roleService;
        $this->sectionService = $sectionService;
        $this->submissionService = $submissionService;
        $this->teamService = $teamService;
        $this->testCaseService = $testCaseService;
        $this->userSectionRoleService = $userSectionRoleService;
        $this->userService = $userService;
    }

    public function modifyProblemPostAction(Request $request) {
        $user = $this->userService->getCurrentUser();
        if (!get_class($user)) {
            return $this->returnForbiddenResponse("USER DOES NOT EXIST");
        }
        
        /* POST DATA */
        $postData = $request->request->all();
        
        /* ASSIGNMENT/CONTEST */
        $assignmentId = $postData["assignmentId"];
        if (!isset($assignmentId) || !($assignmentId > 0)) {
            return $this->returnForbiddenResponse("ASSIGNMENT ID WAS NOT PROVIDED OR NOT FORMATTED PROPERLY");
        }
        
        $assignment = $this->assignmentService->getAssignmentById($assignmentId);
        if (!$assignment) {
            return $this->returnForbiddenResponse("ASSIGNMENT ".$assignmentId." DOES NOT EXIST");
        }
        
        $elevatedUser = $user->hasRole(Constants::SUPER_ROLE) || $user->hasRole(Constants::ADMIN_ROLE) || $this->graderService->isJudging($user, $assignment->section);
        if (!$elevatedUser) {
            return $this->returnForbiddenResponse("PERMISSION DENIED");
        }
        
        /* PROBLEM */
        $problemId = $postData["problemId"];
        $problem = null;
        if (isset($problemId)) { 
            if ($problemId == 0) {
                $problem = $this->problemService->createEmptyProblem();
                $problem->assignment = $assignment;
            } else {
                $problem = $this->problemService->getProblemById($problemId);

                if (!$problem || $assignment != $problem->assignment) {
                    return $this->returnForbiddenResponse("PROBLEM ".$problemId." DOES NOT EXIST");
                }
            }
        } else {			
            return $this->returnForbiddenResponse("PROBLEM ID WAS NOT PROVIDED");
        }
        
        /* DEFAULT CONTEST SETTINGS */
        $problem->version = $problem->version + 1;
        $problem->weight = 1;
        $problem->is_extra_credit = false;
        $problem->total_attempts = 0;
        $problem->attempts_before_penalty = 0;
        $problem->penalty_per_attempt = 0;
        $problem->stop_on_first_fail = false;
        $problem->response_level = Constants::NONE_RESPONSE_LEVEL;
        $problem->display_testcaseresults = false;
        $problem->testcase_output_level = Constants::NONE_TESTCASE_OUTPUT_LEVEL;
        $problem->extra_testcases_display = false;	
        $problem->slaves = new ArrayCollection();
        $problem->master = null;

        /* NAME AND DESCRIPTION */
        $problemName = $postData["name"];
        $problemDescription = $postData["description"];

        if (isset($problemName) && trim($problemName) != "" && isset($problemDescription) && trim($problemDescription) != "") {
            $problem->name = trim($problemName);
            $problem->description = trim($problemDescription);
        } else {
            return $this->returnForbiddenResponse("NAME AND DESCRIPTION NEED TO BE PROVIDED");
        }
        
        /* TIME LIMIT */
        $timeLimit = trim($postData["time_limit"]);
        if (!is_numeric($timeLimit) || $timeLimit < 0 || $timeLimit != round($timeLimit)) {
            return $this->returnForbiddenResponse("TIME LIMIT PROVIDED WAS NOT VALID");
        }

        $problem->time_limit = $timeLimit;
                
        /* PROBLEM LANGUAGES */
        /* remove the old ones */
        $problem->problem_languages->clear();

        foreach ($assignment->contest_languages->toArray() as $contestLanguage) {
            $contestProblemLanguage = $this->problemLanguageService->createProblemLanguage($problem, $contestLanguage);
            $problem->problem_languages->add($contestProblemLanguage);
        }
        
        /* TESTCASES */
        /* Set the old testcases to null
           so they don"t go away and can be accessed in the results page */
        foreach ($problem->testcases as &$testcase) {
            $testcase->problem = null;
            $this->testCaseService->insertTestCase($testcase, false);
        }
        
        $newTestcases = new ArrayCollection();
        $count = 1;
        $testCases = $postData["testcases"];
        foreach ($postData["testcases"] as &$tc) {
            $tc = (array) $tc;

            /* Build the testcase */
            $testcase = $this->testCaseService->createEmptyTestCase();

            $testcase->problem = $problem;
            $testcase->seq_num = $count;
            $testcase->command_line_input = null;
            $testcase->feedback = null;
            $testcase->weight = 1;
            $testcase->is_extra_credit = false;
            
            if (isset($tc["input"]) && trim($tc["input"]) != "" && isset($tc["output"]) && trim($tc["output"]) != "" && isset($tc["sample"])) {    
                $testcase->input = $tc["input"];
                $testcase->correct_output = $tc["output"];
                $testcase->is_sample = ($tc["sample"] == "true");
            } else {
                return $this->returnForbiddenResponse("TESTCASE WAS NOT FORMATTED PROPERLY");
            }
         
            $this->testCaseService->insertTestCase($testcase, false);
            $newTestcases->add($testcase);
            
            $count++;
        }
        $problem->testcases = $newTestcases;
        $problem->testcase_counts[] = count($problem->testcases);
        
        $this->problemService->insertProblem($problem);

        /* Update the leaderboard */
        $assignment->updateLeaderboard($this->graderService, $this->getDoctrine()->getManager());
        $url = $this->generateUrl("contest_problem", [
            "contestId" => $problem->assignment->section->id, 
            "roundId" => $problem->assignment->id, 
            "problemId" => $problem->id
        ]);
                
        $response = new Response(json_encode([
            "id" => $problem->id,
            "redirect_url" => $url,
            "problem" => $problem,
        ]));			
        
        return $this->returnOkResponse($response);
    }
    
    public function modifyContestPostAction(Request $request) {
        $entityManager = $this->getDoctrine()->getManager();

        $user = $this->userService->getCurrentUser();
        if (!get_class($user)) {
            return $this->returnForbiddenResponse("USER DOES NOT EXIST");
        }
        
        /* POST DATA */
        $postData = $request->request->all();
        
        /* COURSE */
        $courseId = $postData["courseId"];
        if (!isset($courseId)) {
            return $this->returnForbiddenResponse("COURSE ID WAS NOT PROVIDED");
        }
        
        $course = $this->courseService->getCourseById($courseId);
        if (!$course) {
            return $this->returnForbiddenResponse("COURSE ".$courseId." DOES NOT EXIST");
        }        
        $elevatedUser = $user->hasRole(Constants::SUPER_ROLE) || $user->hasRole(Constants::ADMIN_ROLE);
        
        $contests = [];

        /* SECTION */
        $contestId = $postData["contestId"];
        if (!isset($contestId)) {
            return $this->returnForbiddenResponse("CONTEST ID WAS NOT PROVIDED");
        } else if ($postData["contestId"] > 0) {
            $section = $this->sectionService->getSectionById($contestId);
            if (!$section || $section->course != $course || !$section->course->is_contest) {
                return $this->returnForbiddenResponse("CONTEST ".$contestId." DOES NOT EXIST");
            }

            $elevatedUser = $elevatedUser || $this->graderService->isJudging($user, $section);

            if (!$elevatedUser) {
                return $this->returnForbiddenResponse("PERMISSION DENIED");
            }
        } else {
            if (!$elevatedUser) {
                return $this->returnForbiddenResponse("PERMISSION DENIED");
            }
            $section = $this->sectionService->createEmptySection();
            
            /* Set up the section */
            $section->course = $course;
            $section->semester = "";
            $section->year = 0;
            $section->is_public = false;
            $section->is_deleted = false;
        }

        /* CONTESTS */
        $contestsToRemove = [];
        
        foreach ($section->assignments as $asgn) {
            $contestsToRemove[$asgn->id] = $asgn;	
        }

        $section->assignments->clear();

        $postContests = (array) json_decode($postData["contests"]);

        if (count($postContests) < 1) {
            return $this->returnForbiddenResponse("Provided contests was empty");
        }

        foreach ($postContests as $postContest) {
            if ($postContest->id) {
                $contest = $this->contestService->getContestById($postContest->id);
                if (!$contest || $contest->section != $section) {
                    return $this->returnForbiddenResponse("ASSIGNMENT ".$postContest->id." DOES NOT EXIST");
                }
            } else {
                $contest = $this->contestService->createEmptyContest();
                $contest->section = $section;
            }

            $contest->name = $postContest->name;
            $contest->description = "";
            $contest->weight = 1;
            $contest->is_extra_credit = false;
            $contest->penalty_per_day = 0;

            /* TIMES */
            $unix_start = strtotime($postContest->times[0]);			
            if (!$unix_start) {
                return $this->returnForbiddenResponse("PROVIDED START DATE IS NOT VALID");
            }			
            $start_date = new DateTime();
            $start_date->setTimestamp($unix_start);

            $unix_end = strtotime($postContest->times[1]);
            if (!$unix_end) {
                return $this->returnForbiddenResponse("PROVIDED END DATE IS NOT VALID");
            }			
            $end_date = new DateTime();
            $end_date->setTimestamp($unix_end);
            
            /* Validate the times */
            if ($start_date >= $end_date) {
                return $this->returnForbiddenResponse("PROVIDED TIMES CONFLICT WITH EACH OTHER");
            }
                        
            /* Build the scoreboard freeze time */
            $freezeMins = trim($postContest->min_freeze);
            $freezeHours = trim($postContest->hour_freeze);
            
            if (!is_numeric($freezeHours) || $freezeHours < 0 || $freezeHours != round($freezeHours)) {
                return $this->returnForbiddenResponse("PROVIDED FREEZE HOURS ARE NOT VALID");
            }
            
            if (!is_numeric($freezeMins) || $freezeMins < 0 || $freezeMins != round($freezeMins)) {
                return $this->returnForbiddenResponse("PROVIDED FREEZE MINUTES ARE NOT VALID");
            }
            
            $dateInterval = DateInterval::createFromDateString($freezeHours." hours + ".$freezeMins." minutes");
            $freeze_date = clone $end_date;	
            $freeze_date->sub($dateInterval);
            
            if (!$freeze_date) {
                return $this->returnForbiddenResponse("CALCULATED FREEZE DATA IS NOT VALID");
            }
            /* Set the freeze time to be the start time if the freeze time is extra long */
            else if ($freeze_date < $start_date) {
                $freeze_date = clone $start_date;
            }
            
            $contest->start_time = $start_date;
            $contest->end_time = $end_date;
            $contest->cutoff_time = $end_date;			
            $contest->freeze_time = $freeze_date;

            $contests[] = $contest;
        }

        foreach ($contests as &$contest) {
            unset($contestsToRemove[$contest->id]);
            $section->assignments->add($contest);
        }

        $section->start_time = clone $contests[0]->start_time;
        $section->start_time->sub(new DateInterval("P30D"));
        
        $section->end_time = clone $contests[count($contests)-1]->end_time;	
        $section->end_time->add(new DateInterval("P14D"));

        /* LANGUAGES */
        $languages = json_decode($postData["languages"]);
        if (count($languages) < 1) {
            return $this->returnForbiddenResponse("AT LEAST ONE LANGUAGE MUST BE PROVIDED");
        }

        foreach ($contests as &$contest) {
            $contest->contest_languages->clear();
        }

        foreach ($languages as $languageId) {
            if (!isset($languageId)) {
                return $this->returnForbiddenResponse("LANGUAGE ID MUST BE PROVIDED");
            }

            $language = $this->languageService->getLanguageById($languageId);
            if (!$language) {
                return $this->returnForbiddenResponse("LANGUAGE ".$languageId." DOES NOT EXIST");
            }
            
            foreach ($contests as &$contest) {
                $contest->contest_languages->add($language);
            }
        }

        /* Reset the languages for all of the problems that already exist */
        foreach ($contests as &$contest) {
            foreach ($contest->problems as &$contestProblem) {
                $contestProblem->problem_languages->clear();
    
                foreach ($contest->contest_languages as $lang) {
                    $contestProblemLanguage = $this->problemLanguageService->createProblemLanguage($contestProblem, $lang);
    
                    $contestProblem->problem_languages->add($contestProblemLanguage);
                }
                $this->problemService->insertProblem($contestProblem);
            }
        }

        /* NAME */
        $contestName = $postData["contest_name"];
        if (!isset($contestName) || trim($contestName) == "") {
            return $this->returnForbiddenResponse("contestId name not provided.");
        }
        
        $section->name = trim($contestName);
            
        /* PENALTY POINTS */
        $penaltyPerWrongAnswer = trim($postData["pen_per_wrong"]);
        if (!is_numeric($penaltyPerWrongAnswer) || $penaltyPerWrongAnswer < 0 || $penaltyPerWrongAnswer != round($penaltyPerWrongAnswer)) {	
            return $this->returnForbiddenResponse("THE PROVIDED PENALTY PER WRONG ANSWER ".$penaltyPerWrongAnswer." IS NOT PERMITTED");
        }

        $penaltyPerCompileError = trim($postData["pen_per_compile"]);
        if (!is_numeric($penaltyPerCompileError) || $penaltyPerCompileError < 0 || $penaltyPerCompileError != round($penaltyPerCompileError)) {
            return $this->returnForbiddenResponse("THE PROVIDED PENALTY PER COMPILE ERROR ".$penaltyPerCompileError." IS NOT PERMITTED");
        }

        $penaltyPerTimeLimit = trim($postData["pen_per_time"]);
        if (!is_numeric($penaltyPerTimeLimit) || $penaltyPerTimeLimit < 0 || $penaltyPerTimeLimit != round($penaltyPerTimeLimit)) {
            return $this->returnForbiddenResponse("THE PROVIDED PENALTY PER TIME LIMIT ".$penaltyPerTimeLimit." IS NOT PERMITTED");
        }

        $penaltyPerRuntimeError = trim($postData["pen_per_runtime"]);
        if (!is_numeric($penaltyPerRuntimeError) || $penaltyPerRuntimeError < 0 || $penaltyPerRuntimeError != round($penaltyPerRuntimeError)) {
            return $this->returnForbiddenResponse("THE PROVIDED PENALTY PER RUNTIME ERROR ".$penaltyPerRuntimeError." IS NOT PERMITTED");
        }

        foreach ($contests as &$contest) {
            $contest->penalty_per_wrong_answer = (int) $penaltyPerWrongAnswer;	
            $contest->penalty_per_compile_error = (int) $penaltyPerCompileError;
            $contest->penalty_per_time_limit = (int) $penaltyPerTimeLimit;
            $contest->penalty_per_runtime_error = (int) $penaltyPerRuntimeError;
        }
        
        /* JUDGES */
        $section->user_roles->clear();	
            
        $judges = json_decode($postData["judges"]);
        $judgeRole = $this->roleService->getRoleByRoleName(Constants::JUDGES_ROLE);
        
        foreach ($judges as $judge) {
            if (isset($judge->id) && isset($judge->name)) {
                if ($judge->id == 0) {
                    /* Validate email */
                    if (!filter_var($judge->name, FILTER_VALIDATE_EMAIL)) {
                        $judge->name = $judge->name."@cedarville.edu";

                        if (!filter_var($judge->name, FILTER_VALIDATE_EMAIL)) {
                            return $this->returnForbiddenResponse("EMAIL ADDRESS ".$judge->name." IS NOT VALID");
                        }
                    }
                    
                    $judgeUser = $this->userService->getUserByObject([
                        "email" => $judge->name,
                    ]);

                    
                    if (!$judgeUser) {
                        $judgeUser = $this->userService->createUser($judge->name, $judge->name);
                        $this->userService->insertUser($judgeUser);
                    }
                } else {
                    $judgeUser = $this->userService->getUserById($judge->id);
                    
                    if (!$judgeUser) {
                        return $this->returnForbiddenResponse("JUDGE ".$judge->id." DOES NOT EXIST");
                    }
                }
                
                $userSectionRole = $this->userSectionRoleService->createUserSectionRole($judgeUser, $section, $judgeRole);
                $section->user_roles->add($userSectionRole);
            } else {
                return $this->returnForbiddenResponse("JUDGE ID NOT FORMATTED PROPERLY");
            }
        }

        /* TEAMS */
        $takeRole = $this->roleService->getRoleByRoleName(Constants::TAKES_ROLE);

        $teams = json_decode($postData["teams"]);
        
        $allMembers = [];

        $newTeams = [];	
        foreach ($contests as $contest) {
            $newTeams[] = new ArrayCollection();
        }

        /* TEAM CREATION */
        foreach ($teams as $team) {
            if (!(isset($team->id) && isset($team->name) && isset($team->members)) ) {
                return $this->returnForbiddenResponse("TEAM DATA WAS NOT FORMATTED PROPERLY");
            }

            if (count($team->id) != count($contests)) {
                return $this->returnForbiddenResponse("TEAM DOES NOT HAVE ENOUGH IDS");
            }

            if (count($team->members) < 1) {
                return $this->returnForbiddenResponse("TEAM DOES NOT HAVE ENOUGH MEMBERS");
            }

            /* GET AN ARRAY OF ALL MEMBERS */
            $members = [];
            foreach ($team->members as $member) {
                if (!(isset($member->id) && isset($member->name))) {
                    return $this->returnForbiddenResponse("MEMBER NOT FORMATTED CORRECTLY");
                }
            
                if ($member->id == 0) {
                    /* Validate email */
                    if (!filter_var($member->name, FILTER_VALIDATE_EMAIL)) {
                        $member->name = $member->name."@cedarville.edu";
                        
                        if (!filter_var($member->name, FILTER_VALIDATE_EMAIL)) {
                            return $this->returnForbiddenResponse("EMAIL ADDRESS ".$member->name." IS NOT VALID");
                        }
                    }
                    
                    $teamUser = $this->userService->getUserByObject([
                        "email" => $member->name,
                    ]);

                    if (!$teamUser) {
                        $teamUser = $this->userService->createUser($member->name, $member->name);
                        $this->userService->insertUser($teamUser);
                    }
                } else {
                    $teamUser = $this->userService->getUserById($member->id);
                    
                    if (!$teamUser) {
                        return $this->returnForbiddenResponse("USER ".$member->id." DOES NOT EXIST");
                    }
                }
                
                $userSectionRole = $this->userSectionRoleService->createUserSectionRole($teamUser, $section, $takeRole);
                $section->user_roles->add($userSectionRole);

                if ($allMembers[$teamUser->getEmail()]) {
                    return $this->returnForbiddenResponse("USER ".$teamUser->getEmail()." CANNOT BE ON TWO TEAMS");
                }

                $allMembers[$teamUser->getEmail()] = $teamUser;

                $members[] = $teamUser;
            }

            /* LOOP THROUGH EACH CONTEST AND ASSIGN MEMBERS */
            $count = 0;
            foreach ($team->id as $teamId) {
                if ($teamId == 0) {
                    $tm = $this->teamService->createEmptyTeam();
                } else {
                    $tm = $this->teamService->getTeamById($teamId);
                    if (!$tm || $tm->assignment != $contests[$count]) {
                        return $this->returnForbiddenResponse("TEAM ".$teamId." DOES NOT EXIST");
                    }
                }

                $tm->assignment = $contests[$count];
                $tm->name = $team->name;
                $tm->workstation_number = $team->workstation_number;
                $tm->users->clear();
                
                foreach ($members as &$member) {
                    $tm->users->add($member);
                }

                $newTeams[$count]->add($tm);

                $count++;
            }
        }

        /* POST-CONTEST CREATION */
        $lastEndDate = clone $contests[count($contests) - 1]->end_time;
        $firstStartDate = clone $contests[0]->start_time;
        $firstEndDate = clone $contests[0]->end_time;

        $postContestId = $postData["post_contest"];
        if (isset($postContestId)) {
            $currentTime = new \DateTime("now");
            
            if ($postContestId == 0 || $currentTime <= $lastEndDate) {
                $postContest = new Assignment();
                $postContest->section = $section;
            } else {
                $postContest = $this->contestService->getContestById($postContestId);
                if (!$postContest || $postContest->section != $section) {
                    return $this->returnForbiddenResponse("POST-CONTEST ASSIGNMENT ".$postContestId." DOES NOT EXIST");
                }
            }

            $postContest->post_contest = true;
            $postContest->name = "Post-Contest";
            $postContest->description = "";
            $postContest->weight = 1;
            $postContest->is_extra_credit = false;
            $postContest->penalty_per_day = 0;

            $postContest->start_time = clone $lastEndDate;			
            $postContest->start_time->add(new DateInterval("P0DT1H"));			
            $postContest->end_time = clone $lastEndDate;
            $postContest->end_time->add(new DateInterval("P180D"));
            $postContest->cutoff_time = clone $lastEndDate;
            $postContest->cutoff_time->add(new DateInterval("P180D"));
            $postContest->freeze_time = clone $lastEndDate;
            $postContest->freeze_time->add(new DateInterval("P180D"));

            $section->end_time = clone $postContest->end_time;

            unset($contestsToRemove[$postContest->id]);
            $entityManager->persist($postContest);	
        }

        /* PRE-CONTEST CREATION */
        $preContestId = $postData["pre_contest"];
        if (isset($preContestId)) {
            if ($preContestId == 0) {
                $preContest = $this->contestService->createEmptyContest();
                $preContest->section = $section;
            } else {
                $preContest = $this->contestService->getContestById($preContestId);
                if (!$preContest || $preContest->section != $section) {
                    return $this->returnForbiddenResponse("PRE-CONTEST ASSIGNMENT ".$preContestId." DOES NOT EXIST");
                }
            }
            
            $preContest->pre_contest = true;
            $preContest->name = "Pre-Contest";
            $preContest->description = "";
            $preContest->weight = 1;
            $preContest->is_extra_credit = false;
            $preContest->penalty_per_day = 0;

            $preContest->start_time = clone $firstStartDate;	
            $preContest->start_time->sub(new DateInterval("P7D"));		
            $preContest->end_time = clone $firstEndDate;
            $preContest->end_time->sub(new DateInterval("P0DT1H"));
            $preContest->cutoff_time = clone $firstEndDate;
            $preContest->cutoff_time->sub(new DateInterval("P0DT1H"));
            $preContest->freeze_time = clone $firstEndDate;
            $preContest->freeze_time->sub(new DateInterval("P0DT1H"));
            

            /* PRE CONTEST LANGUAGES */
            $preContest->contest_languages->clear();
            
            foreach ($languages as $languageId) {
                $language = $this->languageService->getLanguageById($languageId);
                $preContest->contest_languages->add($language);
            }
    
            /* Reset the languages for all of the problems that already exist */
            foreach ($preContest->problems as &$preContestProblem) {
                $preContestProblem->problem_languages->clear();
    
                foreach ($preContest->contest_languages as $lang) {
                    $preContestProblemLanguage = $this->problemLanguageService->createProblemLanguage($preContestProblem, $lang);
                    $preContestProblem->problem_languages->add($preContestProblemLanguage);
                }
    
                $this->problemService->insertProblem($preContestProblem);
            }

            $toRemove = $preContest->teams->toArray();
            $preContest->teams->clear();

            foreach ($allMembers as $email => $memberUser) {
                $tm = $this->teamService->createEmptyTeam();

                $tm->assignment = $preContest;
                $tm->name = $memberUser->getFullName();
                $tm->workstation_number = 0;
                
                $tm->users->add($memberUser);
                
                $preContest->teams->add($tm);
            }

            foreach ($toRemove as &$teamToRemove) {
                $this->teamService->deleteTeam($teamToRemove);
            }

            unset($contestsToRemove[$preContest->id]);
            $entityManager->persist($preContest);
        }
        
        /* DELETE OLD TEAMS AND CREATE (PERSIST) NEW ONES */
        $count = 0;
        foreach ($contests as &$contest) {
            if ($contest->teams) {
                $toRemove = clone $contest->teams;
            } else {
                $toRemove = new ArrayCollection();
            }

            foreach ($contest->teams as &$contestTeam) {
                $contestTeam->assignment = null;
            }

            foreach ($newTeams[$count] as &$newTeam) {
                $toRemove->removeElement($newTeam);

                $newTeam->assignment = $contest;
                $entityManager->persist($newTeam);
                $this->teamService->insertTeam($newTeam);
            }

            foreach ($toRemove as &$teamToRemove) {
                $this->teamService->deleteTeam($teamToRemove);
            }

            $count++;
        }
        foreach ($contestsToRemove as &$contestToRemove) {
            $this->contestService->deleteContest($contestToRemove);
        }

        $this->sectionService->insertSection($section);

        $entityManager = $this->getDoctrine()->getManager();
        foreach ($section->assignments as &$asgn) {
            $asgn->updateLeaderboard($this->graderService, $entityManager);
        }

        /* SOCKET PUSHER */
        $pusher = new SocketPusher($this->container->get("gos_web_socket.wamp.pusher"), $entityManager, $contests[0]);
        $pusher->sendPromptUpdate();
        
        $url = $this->generateUrl("contest", [
            "contestId" => $section->id
        ]);
                
        $response = new Response(json_encode([
            "id" => $section->id,
            "redirect_url" => $url,
            "section" => $section,
        ]));

        return $this->returnOkResponse($response);
    }
    
    public function postQuestionAction(Request $request) {
        $user = $this->userService->getCurrentUser();
        if (!get_class($user)) {
            $this->returnForbiddenResponse("USER DOES NOT EXIST");
        }
        /* See which fields were included */
        $postData = $request->request->all();

        $contestId = $postData["contestId"];
        $contest = $this->contestService->getContestById($contestId);
        
        if (!$contest) {
            return $this->returnForbiddenResponse("CONTEST ".$contestId." DOES NOT EXIST");
        }
        
        $section = $contest->section;
        
        /* Validation */
        $elevatedUser = $this->graderService->isJudging($user, $section) || $user->hasRole(Constants::SUPER_ROLE) || $user->hasRole(Constants::ADMIN_ROLE);

        if (!($elevatedUser ||
             ($this->graderService->isTaking($user, $section) && $section->isActive())
            )) {
            return $this->returnForbiddenResponse("PERMISSION DENIED");
        }
        
        $problemId = $postData["problemId"];
        if (isset($problemId)) {
            $problem = $this->problemService->getProblemById($problemId);
            
            if (!$problem || $problem->assignment != $contest) {
                return $this->returnForbiddenResponse("PROBLEM ".$problemId." DOES NOT EXIST");
            }
        }
        
        $questionId = $postData["question"];
        if (!isset($questionId) || trim($questionId) == "") {
            return $this->returnForbiddenResponse("QUESTION ".$questionId." WAS NOT PROVIDED");
        }
        
        $query = new Query();
        
        if ($problem) {
            $query->problem = $problem;
        } else {
            $query->assignment = $contest;
        }
        
        $query->question = trim($questionId);
        $query->timestamp = new \DateTime("now");
        $query->asker = $this->graderService->getTeam($user, $contest);
        
        $this->queryService->insertQuery($query);

        /* SOCKET PUSHER */
        $entityManager = $this->getDoctrine()->getManager();
        $pusher = new SocketPusher($this->container->get("gos_web_socket.wamp.pusher"), $entityManager, $contest);
        $pusher->sendNewClarification($query);

        $response = new Response(json_encode([
            "id" => $query->id, 
        ]));

        return $this->returnOkResponse($response);
    }
        
    public function scoreboardFreezeAction(Request $request) {
        $user = $this->userService->getCurrentUser();
        if (!get_class($user)) {
            return $this->returnForbiddenResponse("USER DOES NOT EXIST");
        }
        /* See which fields were included */
        $postData = $request->request->all();
        
        $contestId = $postData["contestId"];
        $contest = $this->contestService->getContestById($contestId);
        
        if (!$contest) {
            return $this->returnForbiddenResponse("CONTEST ".$contestId." DOES NOT EXIST");
        }
        
        $section = $contest->section;
        
        /* Validation */
        $elevatedUser = $user->hasRole(Constants::SUPER_ROLE) || $user->hasRole(Constants::ADMIN_ROLE) || $this->graderService->isJudging($user, $section);
        if (!$elevatedUser) {
            return $this->returnForbiddenResponse("YOU ARE NOT ALLOWED TO MODIFY THE SCOREBOARD");
        }
        
        /* Get the type */
        $scoreboardActionType = $postData["type"];
        if (!isset($scoreboardActionType)) {
            return $this->returnForbiddenResponse("SCOREBARD ACTION TYPE WAS NOT PROVIDED");
        }

        $currentTime = new \DateTime("now");
        $frozen = ($contest->freeze_time <= $currentTime);

        switch ($scoreboardActionType) {
            case Constants::SCOREBOARD_FREEZE_ACTION:
                /* Scoreboard is naturally open */
                if (!$frozen) {
                    /* Scoreboard is frozen at this moment so only submissions at this moment and before can be seen */
                    $contest->freeze_override_time = $currentTime;
                    $contest->freeze_override = true;                
                }
                /* Scoreboard is already overriden, undo the changes */
                else if ($contest->freeze_override && $contest->freeze_override_time == null) {
                    $contest->freeze_override_time = null;
                    $contest->freeze_override = false;
                }
                /* Error */
                else {
                    return $this->returnForbiddenResponse("SCOREBOARD IS ALREADY FROZEN");
                }
                
                $shouldFreeze = true;
                break;
            case Constants::SCOREBOARD_UNFREEZE_ACTION:
                /* Scoreboard is naturally frozen */
                if ($frozen) {
                    /* Scoreboard is unfrozen so all submissions can be seen */
                    $contest->freeze_override_time = null;
                    $contest->freeze_override = true;
                } 
                /* Scoreboard is already overriden, undo the changes */
                else if ($contest->freeze_override && $contest->freeze_override_time != null) {
                    $contest->freeze_override_time = null;
                    $contest->freeze_override = false;
                }
                /* Error */
                else {
                    return $this->returnForbiddenResponse("SCOREBOARD IS ALREADY UNFROZEN");
                }
                
                $shouldFreeze = false;
                break;
            default:
            return $this->returnForbiddenResponse("SCOREBOARD ACTION TYPE ".$scoreboardActionType." IS NOT VALID");
        }
         
        $this->contestService->insertContest($contest);

        /* UPDATE LEADERBOARD */
        $contest->updateLeaderboard($this->graderService, $entityManager);

        $entityManager = $this->getDoctrine()->getManager();
        /* SOCKET PUSHER */
        $pusher = new SocketPusher($this->container->get("gos_web_socket.wamp.pusher"), $entityManager, $contest);

        if ($shouldFreeze) {
            $pusher->sendFreeze();
        } else {
            $pusher->sendUnfreeze();
        }
        $pusher->sendScoreboardUpdates(true);

        $response = new Response(json_encode([
            "id" => $contest->id,
            "freeze" => $shouldFreeze,
        ]));

        return $this->returnOkResponse($response);
    }
    
    public function submissionJudgingAction(Request $request) {
        $user = $this->userService->getCurrentUser();
        if (!get_class($user)) {
            $this->returnForbiddenResponse("USER DOES NOT EXIST");
        }
        
        /* See which fields were included */
        $postData = $request->request->all();
        
        $contestId = $postData["contestId"];
        if (!isset($contestId)) {
            return $this->returnForbiddenResponse("CONTEST ".$contestId."DOES NOT EXIST");
        }
        
        $contest = $this->contestService->getContestById($contestId);
        
        if (!$contest) {
            return $this->returnForbiddenResponse("CONTEST ".$contestId." DOES NOT EXIST");
        }
        
        $section = $contest->section;		
        
        $elevatedUser = $user->hasRole(Constants::SUPER_ROLE) || $user->hasRole(Constants::ADMIN_ROLE) || $this->graderService->isJudging($user, $section);
        if (!$elevatedUser) {
            return $this->returnForbiddenResponse("PERMISSION DENIED");
        }

        $entityManager = $this->getDoctrine()->getManager();
        /* SOCKET PUSHER */
        $pusher = new SocketPusher($this->container->get("gos_web_socket.wamp.pusher"), $entityManager, $contest);
        
        /* For submission editing */
        $submissionId = $postData["submissionId"];
        $clarificationId = $postData["clarificationId"];
        if (isset($submissionId)) {
            $submission = $this->submissionService->getSubmissionById($submissionId);
        
            if (!$submission) {
                return $this->returnForbiddenResponse("SUBMISSION ".$submissionId." DOES NOT EXIST");
            }
            
            /* Validation */
            if ($submission->problem->assignment != $contest) {
                return $this->returnForbiddenResponse("PERMISSION DENIED");
            }

            /* Check to make sure the submission hasn"t been claimed */
            /* ************************* RACE CONDITIONS ************************* */
            $isOverride = $postData["override"];
            if ($submission->pending_status > 1 && !$isOverride) {
                return $this->returnForbiddenResponse("SUBMISSION HAS ALREADY BEEN REVIEWED");
            }
            
            $update = true;
            $overrideWrong = false;
            $reviewed = true;

            /* Saying the submission was incorrect */
            $submissionJudgingType = $postData["type"];

            switch ($submissionJudgingType) {
                case Constants::SUBMISSION_JUDGING_WRONG:
                    $overrideWrong = true;
                    break;
                case Constants::SUBMISSION_JUDGING_CORRECT:
                    /* Override the submission to correct */
                    if ($submission->isCorrect(true)) {
                        $submission->wrong_override = false;
                        $submission->correct_override = false;	
                    } else {
                        $submission->wrong_override = false;
                        $submission->correct_override = true;					
                    }
                    break;
                case Constants::SUBMISSION_JUDGING_DELETE:
                    /* Delete the submission */
                    $subId = $submission->id;
                    $this->submissionService->deleteSubmission($submission);
                    break;
                case Constants::SUBMISSION_JUDGING_FORMATTING:
                    $overrideWrong = true;
                            
                    /* Add formatting message to submission */
                    $submission->judge_message = "Formatting Error";
                    break;
                case Constants::SUBMISSION_JUDGING_MESSAGE:
                    $overrideWrong = true;
                    $message = $postData["message"];
                    
                    /* Add custom message to submission */
                    if (!isset($message) || trim($message) == "") {
                        $submission->judge_message = NULL;
                    } else {
                        $submission->judge_message = trim($postData["message"]);
                    }
                    break;
                case Constants::SUBMISSION_JUDGING_CLAIMED:
                    $reviewed = false;
                    
                    if ($submission->pending_status > 0) {
                        return $this->returnForbiddenResponse("Submission has already been claimed");
                    }	
                    
                    $submission->pending_status = 1;

                    $update = false;

                    /* Let the judges know this one has been claimed */
                    $pusher->sendClaimedSubmission($submission->id);
                    break;
                case Constants::SUBMISSION_JUDGING_UNCLAIMED:
                    $reviewed = false;
                    
                    if ($submission->pending_status < 1) {
                        return $this->returnForbiddenResponse("SUBMISSION HAS ALREADY BEEN UN-CLAIMED");
                    }	
                    
                    $submission->pending_status = 0;

                    $update = false;	
                    
                    /* Let the other judges know this submission is back on the market */
                    $pusher->sendNewSubmission($submission);
                    break;
                default:
                    return $this->returnForbiddenResponse("TYPE OF JUDGING COMMAND NOT ALLOWED");
            }

            /* Do this if you need to override the submission to be wrong */
            /* (since it is used in many of the cases above) */
            if ($overrideWrong) {
                /* Override the submission to wrong */
                if ($submission->isCorrect(true) || $submission->isError()) {
                    $submission->wrong_override = true;
                    $submission->correct_override = false;
                } else {
                    $submission->wrong_override = false;
                    $submission->correct_override = false;
                }
            }

            if ($reviewed) {
                $submission->pending_status = 2;
            }
            
            $submission->reviewer = $user;
            
            $submission->edited_timestamp = new \DateTime("now");
            
            $this->submissionService->insertSubmission($submission);

            if ($update) {
                /* UPDATE LEADERBOARD */
                $entityManager = $this->getDoctrine()->getManager();
                $contest->updateLeaderboard($this->graderService, $entityManager);

                if ($submissionJudgingType != Constants::SUBMISSION_JUDGING_DELETE) {
                    $pusher->sendGradedSubmission($submission);
                    $pusher->sendResultUpdate($submission);
                    $pusher->sendScoreboardUpdates();
                }

                if ($submissionJudgingType == Constants::SUBMISSION_JUDGING_DELETE) {
                    $type = "delete";
                } else if ($submissionJudgingType != Constants::SUBMISSION_JUDGING_CORRECT) {
                    $type = "reject";
                } else {
                    $type = "accept";
                }

                $pusher->sendResponse($submission, $type);
            }
            
            $response = new Response(json_encode([
                "id" => ($submission) ? $submission->id : $subId,
                "reviewed" => $reviewed, 
            ]));
            
            return $this->returnOkResponse($response);
        } 
        /* For clarification editing */
        else if (isset($clarificationId)) {
            /* Posting a notice */
            if ($clarificationId == 0) {
                $query = $this->queryService->createEmptyQuery();
                $query->assignment = $contest;
                $query->answerer = $user;
                $query->timestamp = new \DateTime("now");
                $this->queryService->insertQuery($query);
            }
            /* Answering a query */
            else {
                $query = $this->queryService->getQueryById($clarificationId);
                
                if (!$query) {
                    return $this->returnForbiddenResponse("CLARIFICATION ".$clarificationId." DOES NOT EXIST");
                }
                
                if (!(
                        (isset($query->problem) && $query->problem->assignment == $contest) || 
                        (isset($query->assignment) && $query->assignment == $contest))
                    ) {
                    return $this->returnForbiddenResponse("PERMISSION DENIED");
                }
            
                $query->answerer = $user;
                if ($postData["global"]) {
                    $query->asker = null;
                }
            }

            $answerId = $postData["answer"];
            
            /* Add answer to the query */
            if (!isset($answerId)) {
                return $this->returnForbiddenResponse("ANSWER ".$answerId." DOES NOT EXIST");
            }
            
            $query->answer = $answerId;
            $queryId = $query->id;        
            $entityManager->flush();
            
            /* Push a clarification message */
            $pusher->sendClarification($query);

            $response = new Response(json_encode([
                "id" => $queryId,
                "answered" => $answered, 
            ]));
            return $this->returnOkResponse($response);
        }
        /* For removing all submissions */
        else if ($postData["type"] == "clear-subs") {
            if ($contest->isActive()) {
                return $this->returnForbiddenResponse("CANNOT DO THIS WHILE THE CONTEST IS RUNNING");
            }

            $result = $this->submissionService->deleteAllSubmissionsForAssignmentClearSubmissions($contest->problems->toArray());
            
            $entityManager = $this->getDoctrine()->getManager();
            $contest->updateLeaderboard($this->graderService, $entityManager);

            $response = new Response(json_encode([
                "good" => true,
            ]));
            return $this->returnOkResponse($response);
        }
        /* For removing all clarifications */
        else if ($postData["type"] == "clear-clars") {
            if ($contest->isActive()) {
                return $this->returnForbiddenResponse("CANNOT DO THIS WHILE THE CONTEST IS RUNNING");
            }

            $result = $this->queryService->deleteQueriesByProblemsAndAssignment($contest->problems->toArray(), $contest);

            $entityManager = $this->getDoctrine()->getManager();
            $contest->updateLeaderboard($this->graderService, $entityManager);
            
            $response = new Response(json_encode([
                "good" => true,
            ]));
            return $this->returnOkResponse($response);
        }
        // error
        else {
            return $this->returnForbiddenResponse("SUBMISSION OR CLARIFICATION ID NOT PROVIDED");
        }
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
    
    private function logError($message) {
        $errorMessage = "ContestPostController: ".$message;
        $this->logger->error($errorMessage);
        return $errorMessage;
    }
}

?>
