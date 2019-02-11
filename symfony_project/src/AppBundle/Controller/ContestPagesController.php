<?php

namespace AppBundle\Controller;

use \DateTime;
use \DateInterval;

use AppBundle\Constants;

use AppBundle\Entity\User;
use AppBundle\Entity\Course;
use AppBundle\Entity\UserSectionRole;
use AppBundle\Entity\Role;
use AppBundle\Entity\Query;
use AppBundle\Entity\Section;
use AppBundle\Entity\Assignment;
use AppBundle\Entity\Submission;
use AppBundle\Entity\Problem;
use AppBundle\Entity\Team;
use AppBundle\Entity\Testcase;
use AppBundle\Entity\ProblemLanguage;

use AppBundle\Service\ContestService;
use AppBundle\Service\CourseService;
use AppBundle\Service\GraderService;
use AppBundle\Service\LanguageService;
use AppBundle\Service\ProblemService;
use AppBundle\Service\QueryService;
use AppBundle\Service\RoleService;
use AppBundle\Service\SectionService;
use AppBundle\Service\SubmissionService;
use AppBundle\Service\TeamService;
use AppBundle\Service\TrialService;
use AppBundle\Service\UserSectionRoleService;
use AppBundle\Service\UserService;

use AppBundle\Utils\Grader;

use Doctrine\Common\Collections\ArrayCollection;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class ContestPagesController extends Controller {
    private $contestService;
    private $courseService;
    private $entityManager;
    private $languageService;
    private $logger;
    private $problemService;
    private $queryService;
    private $sectionService;
    private $submissionService;
    private $userSectionRoleService;
    private $userService;

    public function __construct(ContestService $contestService,
                                CourseService $courseService,
                                EntityManagerInterface $entityManager,
                                GraderService $graderService,
                                LanguageService $languageService,
                                LoggerInterface $logger,
                                ProblemService $problemService,
                                QueryService $queryService,
                                RoleService $roleService,
                                SectionService $sectionService,
                                SubmissionService $submissionService,
                                TeamService $teamService,
                                TrialService $trialService,
                                UserSectionRoleService $userSectionRoleService,
                                UserService $userService) {
        $this->contestService = $contestService;
        $this->courseService = $courseService;
        $this->entityManager = $entityManager;
        $this->graderService = $graderService;
        $this->languageService = $languageService;
        $this->logger = $logger;
        $this->problemService = $problemService;
        $this->queryService = $queryService;
        $this->roleService = $roleService;
        $this->sectionService = $sectionService;
        $this->submissionService = $submissionService;
        $this->teamService = $teamService;
        $this->trialService = $trialService;
        $this->userSectionRoleService = $userSectionRoleService;
        $this->userService = $userService;
    }

    public function contestAction($contestId, $roundId) {
        $user = $this->userService->getCurrentUser();

        if (!$user) {
            return returnForbiddenResponse("USER DOES NOT EXIST");
        }

        /* VALIDATION */
        $section = $this->sectionService->getSectionById($contestId);
        if (!$section || !$section->course->is_contest) {
            return returnForbiddenResponse("CONTEST DOES NOT EXIST!");
        }
        
        $elevatedUser = $this->graderService->isJudging($user, $section) || $user->hasRole("ROLE_SUPER") || $user->hasRole("ROLE_ADMIN");

        /* Elevated or taking and active */
        if( !($elevatedUser || ($this->graderService->isTaking($user, $section) && $section->isActive())) ){
            return $this->returnForbiddenResponse("PERMISSION DENIED");
        }

        /* GET CURRENT CONTEST */
        $allContests = $section->assignments->toArray();	
        $currTime = new \DateTime('now');
        
        $current = null;
        /* Decide the round for the users */
        if ($roundId == 0) {
            /* if the round was not provided, we need to default to the proper contest for them
               get the one that will start next/is currently going on */
            foreach ($allContests as $cont) {
                // choose the one that ends next
                if ($currTime <= $cont->end_time) {
                    $current = $cont;
                    break;
                }
            }
            
            /* If all the contests are past, get the final one */
            if (!$current) {
                $current = $allContests[count($allContests) - 1];
            }			
        }
        /* Use the round provided */
        else {			
            $current = $this->contestService->getContestById($roundId);
        }
    
        if (!$current || $current->section != $section) {
            return returnForbiddenResponse("ROUND DOES NOT EXIST");
        }

        /* Check to see if you need to populate the post contest */
        if ($current->post_contest) {
            $previous = $allContests[count($allContests) - 2];
            
            if (!$current->is_cloned && isset($previous) && $previous->isFinished() && $current->isActive()) {
                $current->is_cloned = true;

                /* Create problems */
                $newProbs = [];
                $prevProbs = $previous->problems->toArray();
                foreach ($prevProbs as $prevProb) {
                    $prb = clone $prevProb;				
                    $prb->assignment = $current;

                    $this->problemService->insertProblem($prb, false);

                    $newProbs[$prevProb->id] = $prb;
                }				
                
                /* Create teams */
                $prevTeams = $previous->teams->toArray();
                foreach ($prevTeams as $prevTeam) {
                    $prevSubs = $prevTeam->submissions->toArray();

                    foreach ($prevTeam->users as $prevUser) {
                        $tm = new Team();
                        $tm->assignment = $current;
                        $tm->name = $prevUser->getFullName();
                        $tm->workstation_number = 0;
                        $tm->users->add($prevUser);

                        $this->teamService->insertTeam($tm, false);

                        foreach ($prevSubs as $prevSub) {
                            $sb = clone $prevSub;
                            $sb->problem = $newProbs[$sb->problem->id];
                            $sb->team = $tm;							

                            $this->submissionService->insertSubmission($sb, false);
                        }
                    }
                }
            
                /* Create queries/answers */
                $prevQueries = $previous->queries->toArray();
                foreach ($prevQueries as $prevQuery) {
                    $qry = clone $prevQuery;
                    $qry->assignment = $current;

                    $current->queries->add($qry);
                }

                $current->updateLeaderboard($this->graderService, $this->entityManager);

                $this->contestService->insertContest($current);
            }
        }
        
        $team = $this->graderService->getTeam($user, $current);
                
        /* Set open/not open */
        if ($elevatedUser || ($current->start_time <= $currTime)) {
            $contest_open = true;
        } else {
            $contest_open = false;
        }
        
        /* GET ALL USERS */
        $section_takers = $section->getAllUsers();
        
        return $this->render("contest/hub.html.twig", [
            "user" => $user,
            "team" => $team,
            "section" => $section,
            "grader" => $this->graderService,
            "user_impersonators" => $section_takers,
            "current_contest" => $current,
            "contests" => $allContests,
            "elevatedUser" => $elevatedUser
        ]);
    }

    public function problemAction($contestId, $roundId, $problemId) {
        $user = $this->userService->getCurrentUser();
        if (!$user) {
            return returnForbiddenResponse("USER DOES NOT EXIST");
        }

        /* VALIDATION */
        $section = $this->sectionService->getSectionById($contestId);
        if (!$section || !$section->course->is_contest) {
            return returnForbiddenResponse("404 - CONTEST DOES NOT EXIST!");
        }
        
        $round = $this->contestService->getContestById($roundId);
        if (!$round || $round->section != $section) {
            return returnForbiddenResponse("404 - ASSIGNMENT DOES NOT EXIST!");
        }
        
        $problem = $this->problemService->getProblemById($problemId);
        if (!$problem || $problem->assignment != $round) {
            return returnForbiddenResponse("404 - PROBLEM DOES NOT EXIST!");
        }

        $elevatedUser = $this->graderService->isJudging($user, $section) || $user->hasRole("ROLE_SUPER") || $user->hasRole("ROLE_ADMIN");

        /* Elevated or taking and open */
        if (!($elevatedUser || ($this->graderService->isTaking($user, $section) && $round->isOpened()))) {
            return $this->redirectToRoute("contest", [
                "contestId" => $section->id, 
                "roundId" => $round->id
            ]);
        }
        
        /* Get JSON info for language info */
        $problem_languages = $problem->problem_languages->toArray();
        
        $languages = [];
        $default_code = [];
        $ace_modes = [];
        $filetypes = [];
        
        foreach ($problem_languages as $pl) {
            $languages[] = $pl->language;
            
            $ace_modes[$pl->language->name] = $pl->language->ace_mode;
            $filetypes[str_replace(".", "", $pl->language->filetype)] = $pl->language->name;
            
            /* Either get the default code from the problem or from the overall default */
            if($pl->default_code != null){
                $default_code[$pl->language->name] = $pl->deblobinateDefaultCode();
            } else{
                $default_code[$pl->language->name] = $pl->language->deblobinateDefaultCode();
            }
        }

        $team = $this->graderService->getTeam($user, $round);
        
        /* Get the list of all submissions by the team/user */
        if($team){
            $all_submissions = $this->submissionService->getSubmissionsByObject([
                'team' => $team,
                'problem' => $problem,
                'is_completed' => true,
            ], [
                'timestamp'=>'DESC'
            ]);
        }
        /* No team, so it is just a user (judge) */
        else {
            $all_submissions = $this->submissionService->getSubmissionsByObject([
                'user' => $user,
                'problem' => $problem,
                'is_completed' => true,
            ], ['timestamp'=>'DESC']);
        }
        
        /* Get the trial for the problem */
        $trial = $this->trialService->getTrialByObject([
            'user' => $user,
            'problem' => $problem,
        ]);
        
        /* Get the queries */
        if ($elevatedUser) {
            $extra_query = "OR 1=1";
        } else {
            $extra_query = "";
        }
        
        $queries = $this->queryService->getQueriesForContestPagesProblem($extra_query, $problem, $team);
        
        /* Set open/not open */
        $currTime = new \DateTime("now");
        if ($elevatedUser || ($round->start_time <= $currTime)) {
            $contest_open = true;
        } else {
            $contest_open = false;
        }
        
        if (!$contest_open) {
            return $this->redirectToRoute("contest", [
                "contestId" => $round->section->id, 
                "roundId" => $round->id
            ]);
        }
        
        /* Submission updating trial */
        $submissionId = $_GET["submissionId"];
        if (isset($submissionId)) {
            $submission = $this->submissionService->getSubmissionById($submissionId);
            
            $sameTeam = true;
            $sameUser = true;
            if ($submission->team) {
                $team = $this->graderService->getTeam($user, $submission->problem->assignment);
                $sameTeam = ($team == $submission->team);
            } else {
                $sameUser = ($user == $submission->user);
            }
            
            if (!$elevatedUser && !($sameTeam || $sameUser || $submission->problem == $problem)) {
                return returnForbiddenResponse("YOU ARE NOT ALLOWED TO EDIT THE SUBMISSION ON THIS PROBLEM");
            }
            
            if (!$trial) {
                $trial = new Trial();
                
                $trial->user = $user;
                $trial->problem = $problem;
                $trial->language = $submission->language;			
                $trial->show_description = true;
                
                $this->trialService->insertTrial($trial, false);
            }
            
            $trial->file = $submission->submitted_file;
            
            $trial->filename = $submission->filename;
            $trial->main_class = $submission->main_class_name;
            $trial->package_name = $submission->package_name;
            $trial->last_edit_time = new \DateTime("now");
        }
                                        
        return $this->render("contest/problem.html.twig", [
            "user" => $user,
            "team" => $team,
            "section" => $section,
            "current_contest" => $round,
            "contest_open" => $contest_open,
            "problem" => $problem,
            "trial" => $trial,
            "queries" => $queries,
            "grader" => $grader,
            "all_submissions" => $all_submissions,
            "languages" => $languages,
            "default_code" => $default_code,
            "ace_modes" => $ace_modes,
            "filetypes" => $filetypes,
        ]);
    }
    
    public function judgingAction($contestId, $roundId) {
        $user = $this->userService->getCurrentUser();
        if (!$user) {
            return returnForbiddenResponse("USER DOES NOT EXIST");
        }

        $section = $this->sectionService->getSectionById($contestId);
        if (!$section || !$section->course->is_contest) {
            return returnForbiddenResponse("CONTEST DOES NOT EXIST!");
        }
        
        $allContests = $section->assignments;
        
        /* Get the current contest (see contestAction for a duplicate function) */
        $current = $this->contestService->getContestById($roundId);
        
        if (!$current || $current->section != $section) {
            return returnForbiddenResponse("404 - CONTEST DOES NOT EXIST");
        }	
        
        $elevatedUser = $this->graderService->isJudging($user, $section) || $user->hasRole("ROLE_SUPER") || $user->hasRole("ROLE_ADMIN");

        /* Elevated */
        if (!($elevatedUser)) {
            return $this->returnForbiddenResponse("PERMISSION DENIED");
        }

        /* ALL OF THESE ARE VERY SPECIFIC AND NOT BEING MOVED TO SERVICES */
        /* Get the pending submissions */
        $qb_pending = $this->entityManager->createQueryBuilder();
        $qb_pending->select("s")
            ->from("AppBundle\Entity\Submission", "s")
            ->where("s.problem IN (?1)")
            ->andWhere("s.pending_status = ?2")
            ->andWhere("s.is_completed = ?3")
            ->orderBy("s.timestamp", "ASC")
            ->setParameter(1, $current->problems->toArray())
            ->setParameter(2, 0)
            ->setParameter(3, true);
        $pending_subs = $qb_pending->getQuery()->getResult();
                
        $qb_finished = $this->entityManager->createQueryBuilder();
        $qb_finished->select("s")
            ->from("AppBundle\Entity\Submission", "s")
            ->where("s.problem IN (?1)")
            ->andWhere("s.pending_status = ?2")
            ->andWhere("s.is_completed = ?3")
            ->andWhere("s.team IS NOT NULL")
            ->orderBy("s.timestamp", "ASC")
            ->setParameter(1, $current->problems->toArray())
            ->setParameter(2, 2)
            ->setParameter(3, true);
        $finished_subs = $qb_finished->getQuery()->getResult();
        
        # get user"s claimed subs
        $qb_claimed = $this->entityManager->createQueryBuilder();
        $qb_claimed->select("s")
            ->from("AppBundle\Entity\Submission", "s")
            ->where("s.problem IN (?1)")
            ->andWhere("s.pending_status = ?2")
            ->andWhere("s.reviewer = ?3")
            ->andWhere("s.is_completed = ?4")
            ->orderBy("s.timestamp", "ASC")
            ->setParameter(1, $current->problems->toArray())
            ->setParameter(2, 1)
            ->setParameter(3, $user)
            ->setParameter(4, true);
        $claimed_subs = $qb_claimed->getQuery()->getResult();

        # get the queries for the contest
        $qb_clars = $this->entityManager->createQueryBuilder();
        $qb_clars->select("s")
            ->from("AppBundle\Entity\Query", "s")
            ->where("s.problem IN (?1)")
            ->orWhere("s.assignment IN (?2)")
            ->andWhere("s.answer IS NULL")
            ->orderBy("s.timestamp", "ASC")
            ->setParameter(1, $current->problems->toArray())
            ->setParameter(2, $current);
        $clarifications = $qb_clars->getQuery()->getResult();
        
        // get the answered queries for the contest
        $qb_ans = $this->entityManager->createQueryBuilder();
        $qb_ans->select("s")
            ->from("AppBundle\Entity\Query", "s")
            ->where("s.problem IN (?1)")
            ->orWhere("s.assignment IN (?2)")
            ->andWhere("s.answer IS NOT NULL")
            ->orderBy("s.timestamp", "ASC")
            ->setParameter(1, $current->problems->toArray())
            ->setParameter(2, $current);
        $answered_clarifications = $qb_ans->getQuery()->getResult();

        return $this->render("contest/judging.html.twig", [
            "section" => $section,
            "grader" => $grader,
            "elevatedUser" => $elevatedUser,
            "current_contest" => $current,
            "contests" => $allContests,
            "contest_open" => true,
            "pending_subs" => $pending_subs,
            "claimed_subs" => $claimed_subs,
            "finished_subs" => $finished_subs,
            "pending_clars" => $clarifications,
            "finished_clars" => $answered_clarifications,
            "section_takers" => $section_takers,
            "section_judges" => $section_judges,
        ]);	
    }	
        
    public function problemEditAction($contestId, $roundId, $problemId) {
        $user = $this->userService->getCurrentUser();
        if (!$user) {
            return returnForbiddenResponse("USER DOES NOT EXIST");
        }
        
        $section = $this->sectionService->getSectionById($contestId);
        if (!$section || !$section->course->is_contest) {
            return returnForbiddenResponse("SECTION (CONTEST) DOES NOT EXIST!");
        }
        
        $contest = $this->contestService->getContestById($roundId);
        if (!$contest || $contest->section != $section) {
            return returnForbiddenResponse("ASSIGNMENT (ROUND) DOES NOT EXIST!");
        }
        
        if ($problemId != 0) {
            $problem = $this->problemService->getProblemById($problemId);
            if (!$problem || $problem->assignment != $contest) {
                return returnForbiddenResponse("PROBLEM DOES NOT EXIST!");
            }
        } else {
            $problem = null;			
        }
        
        $elevatedUser = $this->graderService->isJudging($user, $section) || $user->hasRole("ROLE_SUPER") || $user->hasRole("ROLE_ADMIN");

        /* Elevated  */
        if (!($elevatedUser)) {
            return $this->returnForbiddenResponse("PERMISSION DENIED");
        }
        
        $default_code = [];
        $ace_modes = [];
        $filetypes = [];
        
        $languages = $this->languageService->getAll();
        foreach ($languages as $l) {
            $ace_modes[$l->name] = $l->ace_mode;
            $filetypes[str_replace(".", "", $l->filetype)] = $l->name;
            
            /* Either get the default code from the problem or from the overall default */
            $default_code[$l->name] = $l->deblobinateDefaultCode();
        }
        
        return $this->render("contest/problem_edit.html.twig", [
            "contest" => $contest,
            "current" => $contest,
            "current_contest" => $contest, 
            "problem" => $problem,
            "edit_route" => true,
            "languages" => $languages,
            "ace_modes" => $ace_modes,
            "filetypes" => $filetypes,
            "default_code" => $default_code,
        ]);
    }
    
    public function contestEditAction($contestId) {
        $user = $this->userService->getCurrentUser();
        if (!$user) {
            return returnForbiddenResponse("USER DOES NOT EXIST");
        }

        /* VALIDATION */
        if ($contestId != 0) {
            $section = $this->sectionService->getSectionById($contestId);

            if (!$section || !$section->course->is_contest) {
                return returnForbiddenResponse("404 - SECTION (CONTEST) DOES NOT EXIST!");
            }
            
            $course = $section->course;
            
            $elevatedUser = $this->graderService->isJudging($user, $section) || $user->hasRole("ROLE_SUPER") || $user->hasRole("ROLE_ADMIN");
                    
            /* Get the judges */
            $judgeRole = $this->roleService->getRoleByRoleName(Constants::JUDGES_ROLE);
            $judges = $this->userSectionRoleService->getUserSectionRolesByObject([
                "section" => $section,
                "role" => $judgeRole,
            ]);
            
            /* Get freeze time diff */
            $di = $section->assignments[1]->end_time->diff($section->assignments[1]->freeze_time);
        
            $freeze_diff_minutes = $di->i;
            $freeze_diff_hours = ($di->days * 24) + $di->h;
        } else {
            $courseId = $_GET['courseId'];
            $course = $this->courseService->getCourseById($courseId);
            
            if (!$course->is_contest) {
                return $this->returnForbiddenResponse('PERMISSION DENIED');
            }
            
            $section = null;
            $freeze_diff_hours = 1;
            $freeze_diff_minutes = 0;
            
            $judges = [];
            $elevatedUser = $user->hasRole(Constants::ADMIN_ROLE) || $user->hasRole(Constants::SUPER_ROLE);
        }
        
        if (!($elevatedUser)) {
            return $this->returnForbiddenResponse("PERMISSION DENIED");
        }

        $languages = $this->languageService->getAll();
        
        return $this->render("contest/edit.html.twig", [
            "course" => $course,
            "section" => $section,
            "freeze_diff_hours" => $freeze_diff_hours,
            "freeze_diff_minutes" => $freeze_diff_minutes,
            "languages" => $languages,
            "judges" => $judges,
            "elevatedUser" => $elevatedUser
        ]);
    }

    public function resultAction($contestId, $roundId, $problemId, $resultId) {
        $user = $this->userService->getCurrentUser();
        if (!$user) {
            return returnForbiddenResponse("USER DOES NOT EXIST");
        }
        
        $section = $this->sectionService->getSectionById($contestId);
        if (!$section || !$section->course->is_contest) {
            return returnForbiddenResponse("404 - CONTEST DOES NOT EXIST!");
        }
        
        $round = $this->contestService->getContestById($roundId);
        if (!$round || $round->section != $section) {
            return returnForbiddenResponse("404 - ASSIGNMENT DOES NOT EXIST!");
        }
        
        $problem = $this->problemService->getProblemById($problemId);
        if (!$problem || $problem->assignment != $round) {
            return returnForbiddenResponse("404 - PROBLEM DOES NOT EXIST!");
        }	

        $submission = $this->submissionService->getSubmissionById($resultId);
        if (!$submission || $submission->problem != $problem || !$submission->is_completed) {
            return returnForbiddenResponse("404 - SUBMISSION DOES NOT EXIST");
        }

        $elevatedUser = $this->graderService->isJudging($user, $section) || $user->hasRole(Constants::SUPER_ROLE) || $user->hasRole(Constants::ADMIN_ROLE);

        $team = $this->graderService->getTeam($user, $round);
        
        /* Elevated or on submission team */
        if (!($elevatedUser || $team == $submission->team)) {
            return $this->returnForbiddenResponse("PERMISSION DENIED");
        }
        
        $ace_mode = $submission->language->ace_mode;
        
        return $this->render("contest/result.html.twig", [
            "user" => $user,
            "team" => $team,
            "problem" => $problem,
            "current_contest" => $round,
            "section" => $round->section,
            "submission" => $submission,
            "ace_mode" => $ace_mode,
            "contest_open" => true,
            "result_route" => true,
            "grader" => $this->graderService
        ]);
    }
    
    public function scoreboardAction($contestId, $roundId) {
        $user = $this->userService->getCurrentUser();
        if (!$user) {
            return returnForbiddenResponse("404 - USER DOES NOT EXIST");
        }

        /* VALIDATION */
        $section = $this->sectionService->getSectionById($contestId);

        if (!$section || !$section->course->is_contest) {
            return returnForbiddenResponse("404 - CONTEST DOES NOT EXIST!");
        }
        
        $round = $this->contestService->getContestById($roundId);
        if (!$round || $round->section != $section) {
            return returnForbiddenResponse("404 - ROUND DOES NOT EXIST!");
        }
        
        if (is_object($user)) {
            $elevatedUser = $this->graderService->isJudging($user, $section) || $user->hasRole(Constants::SUPER_ROLE) || $user->hasRole(Constants::ADMIN_ROLE);
            $team = $this->graderService->getTeam($user, $round);
        } else {
            $elevatedUser = false;
            $team = null;
        }		
        
        /* Elevated or section active */
        if (!($elevatedUser || $section->isActive())) {
            return $this->returnForbiddenResponse("PERMISSION DENIED");
        }
        
        /* Set open/not open */
        if ($elevatedUser || ($current->start_time <= $currTime)) {
            $contest_open = true;
        } else {
            $contest_open = false;
        }
        
        return $this->render("contest/scoreboard.html.twig", [
            "user" => $user,
            "team" => $team,
            "section" => $section,
            "grader" => $grader,
            "current_contest" => $round,
            "contest_open" => $contest_open,
            "elevatedUser" => $elevatedUser
        ]);
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
