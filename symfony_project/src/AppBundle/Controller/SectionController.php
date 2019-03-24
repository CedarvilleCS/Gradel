<?php

namespace AppBundle\Controller;

use \DateInterval;
use \DateTime;

use AppBundle\Constants;

use AppBundle\Entity\Assignment;
use AppBundle\Entity\Course;
use AppBundle\Entity\Role;
use AppBundle\Entity\Section;
use AppBundle\Entity\Submission;
use AppBundle\Entity\User;
use AppBundle\Entity\UserSectionRole;

use AppBundle\Service\AssignmentService;
use AppBundle\Service\CourseService;
use AppBundle\Service\GraderService;
use AppBundle\Service\RoleService;
use AppBundle\Service\ProblemService;
use AppBundle\Service\SectionService;
use AppBundle\Service\SemesterService;
use AppBundle\Service\SubmissionService;
use AppBundle\Service\TeamService;
use AppBundle\Service\TestCaseService;
use AppBundle\Service\UserSectionRoleService;
use AppBundle\Service\UserService;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

use Doctrine\ORM\Tools\Pagination\Paginator;

use Psr\Log\LoggerInterface;

class SectionController extends Controller {
    private $assignmentService;
    private $courseService;
    private $graderService;
    private $logger;
    private $problemService;
    private $roleService;
    private $sectionService;
    private $semesterService;
    private $submissionService;
    private $teamService;
    private $testCaseService;
    private $userSectionRoleService;
    private $userService;

    public function __construct(AssignmentService $assignmentService,
                                CourseService $courseService,
                                GraderService $graderService,
                                LoggerInterface $logger,
                                ProblemService $problemService,
                                RoleService $roleService,
                                SectionService $sectionService,
                                SemesterService $semesterService,
                                SubmissionService $submissionService,
                                TeamService $teamService,
                                TestCaseService $testCaseService,
                                UserSectionRoleService $userSectionRoleService,
                                UserService $userService) {
        $this->assignmentService = $assignmentService;
        $this->courseService = $courseService;
        $this->graderService = $graderService;
        $this->logger = $logger;
        $this->problemService = $problemService;
        $this->roleService = $roleService;
        $this->sectionService = $sectionService;
        $this->semesterService = $semesterService;
        $this->submissionService = $submissionService;
        $this->teamService = $teamService;
        $this->testCaseService = $testCaseService;
        $this->userSectionRoleService = $userSectionRoleService;
        $this->userService = $userService;
    }

    public function sectionAction($sectionId) {
        $user = $this->userService->getCurrentUser();
        if (!get_class($user)) {
            return $this->returnForbiddenResponse("YOU ARE NOT LOGGED IN");
        }
        /* Will get the impersonated user if they are making the call */
        $requestingUser = $this->getUser();

        $section = $this->sectionService->getSectionById($sectionId);
        if (!$section) {
            return $this->returnForbiddenResponse("SECTION ".$sectionId." DOES NOT EXIST!");
        }
        
        /* Redirect to contest if need be */
        if ($section->course->is_contest) {
            return $this->redirectToRoute("contest", ["contestId" => $section->id]);
        }
        
        /* Get all assignments */
        $assignments = $this->assignmentService->getAssignmentsBySection($section);

        /* Get all users */
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
        
        /* Get future assignments */
        $futureAssignments = $this->assignmentService->getAssignmentsSortedByDueDateForSection($section);
        
        /* Gather submissions */
        /* Get all of the problems to get all of the submissions */
        $allProblems = [];
        foreach ($section->assignments as $sectionAssignments) {
            foreach ($sectionAssignments->problems as $problem) {
                $allProblems[] = $problem;
            }
        }
        
        /* Get assignment grades for the student */
        $grades = [];
        $assignmentProblemSubmissions = [];
        $team = [];
        $isTeaching = $this->graderService->isTeaching($requestingUser, $section);
        
        foreach ($sectionTakers as $sectionTaker) {
            $correctSubmissions = [];
            
            $grades[$sectionTaker->id] = $this->graderService->getAllAssignmentGrades($sectionTaker, $section);
            
            foreach ($assignments as $assignment) {
                $assignmentProblems = $assignment->problems;
                $team = $this->graderService->getTeam($sectionTaker, $assignment);

                $teamOrUser = $sectionTaker;
                $whereClause = "s.user = ?1";
                
                if ($team) {
                    $teamOrUser = $team;
                    $whereClause = "s.team = ?1";
                    foreach ($assignmentProblems as $assignmentProblem) {
                        $bestSubmission = $this->submissionService->getBestSubmissionForTeam($assignmentProblem, $team);
                        if ($bestSubmission) {
                            $correctSubmissions[$assignment->id][$assignmentProblem->id] = $bestSubmission->id;
                        }
                    }
                }
                
                /* Set user's individual test case info and also aggregate class's stats for problem completion */
                $totalAssignmentProblems = count($assignmentProblems);
                foreach ($assignmentProblems as $assignmentProblem) {
                    $testCaseInfo = $this->testCaseService->getTestCaseInfoFromTeamOrUserAndProblem($teamOrUser, $whereClause, $assignmentProblem);
                    if ($sectionTaker == $requestingUser) {
                        $assignmentProblem->userTestCaseInfo = $testCaseInfo;
                    }
                    if ($testCaseInfo->numberOfTestCases == $testCaseInfo->numberOfCorrectTestCases) {
                        ++$assignmentProblem->numberOfCompletedStudents;
                    }
                }
            }
            $assignmentProblemSubmissions[$sectionTaker->id] = $correctSubmissions;
        }

        /* Get the users most recent submissions (top 15) */
        $entityManager = $this->getDoctrine()->getManager();		
        $recentSubmissions = $this->submissionService->getRecentResultsForUser($user);

        /* Array of arrays that contain a main text and a subtext that will be used for autocompleting searches
           ["Timothy Smith", "timothyglensmith@cedarville.edu"]
           ["Get the Sum", "Homework #2"]
           ["Wrong Answer", "Incorrect"] */
        $suggestions = [];

        /* Get the users */
        foreach ($sectionTakers as $sectionTaker) {
            $suggestions[] = [$sectionTaker->getFullName(), $sectionTaker->getEmail()];
        }

        /* Get the teachers */
        foreach ($sectionTeachers as $sectionTeacher) {
            $suggestions[] = [$sectionTeacher->getFullName(), $sectionTeacher->getEmail()];
        }

        /* Get the helpers */
        foreach ($sectionHelpers as $sectionHelper) {
            $suggestions[] = [$sectionHelper->getFullName(), $sectionHelper->getEmail()];
        }

        /* Get the problems */
        foreach ($allProblems as $assignmentProblem) {
            $suggestions[] = [$assignmentProblem->name, $assignmentProblem->assignment->name, $assignmentProblem->testcase_counts];			
        }

        /* Get the assignments and teams */
        foreach ($section->assignments as $sectionAssignment) {
            $suggestions[] = [$sectionAssignment->name, ""];			

            foreach ($sectionAssignment->teams as $assignmentTeam) {
                if ($assignmentTeam->users->count() > 1) {
                    $suggestions[] = [$assignmentTeam->name, ""];
                }
            }
        }

        /* Get the correct types */
        $suggestions[] = ["Correct", ""];
        $suggestions[] = ["Incorrect", ""];
        $suggestions[] = ["Wrong Answer", "Incorrect"];
        $suggestions[] = ["Runtime Error", "Incorrect"];
        $suggestions[] = ["Time Limit Error", "Incorrect"];
        $suggestions[] = ["Compile Error", "Incorrect"];
                
        return $this->render("section/index.html.twig", [
            "future_assigs" => $futureAssignments,
            "grader" => $this->graderService,
            "grades" => $grades,
            "isTeaching" => $isTeaching,
            "search_suggestions" => $suggestions,
            "section" => $section,
            "section_helpers" => $sectionHelpers,
            "section_takers" => $sectionTakers,
            "section_teachers" => $sectionTeachers,
            "submissions" => $recentSubmissions,
            "team" => $team,
            "user" => $user,
            "user_impersonators" => $sectionTakers
        ]);
    }
    
    public function editSectionAction($sectionId) {
        $user = $this->userService->getCurrentUser();
        if (!get_class($user)) {
            return $this->returnForbiddenResponse("YOU ARE NOT LOGGED IN");
        }

        if ($sectionId != 0) {
            if (!isset($sectionId) || !($sectionId > 0)) {
                return $this->returnForbiddenResponse("SECTION ID WAS NOT PROVIDED OR NOT FORMATTED PROPERLY");
            }

            $section = $this->sectionService->getSectionById($sectionId);
            if (!$section) {
                return $this->returnForbiddenResponse("SECTION ".$sectionId." DOES NOT EXIST");
            }

            /* Redirect to contest if need be */
            if ($section->course->is_contest) {
                return $this->redirectToRoute("contest_edit", ["contestId" => $section->id]);
            }
            
            $sectionTakerRoles = [];
            $sectionTeacherRoles = [];
    
            $teachesRole = $this->roleService->getRoleByRoleName(Constants::TEACHES_ROLE);
            $takesRole = $this->roleService->getRoleByRoleName(Constants::TAKES_ROLE);
            
            foreach ($section->user_roles as $sectionUserRole) {
                if ($sectionUserRole->role == $takesRole) {
                    $sectionTakerRoles[] = $sectionUserRole;
                } else if ($sectionUserRole->role == $teachesRole) {
                    $sectionTeacherRoles[] = $sectionUserRole;
                }
            }
        }
        
        $courses = $this->courseService->getNonDeletedCourses();
        $users = $this->userService->getAllUsers();
        $instructors = [];

        foreach ($users as $potentialTeacher) {
            if ($potentialTeacher->hasRole(Constants::ADMIN_ROLE) or $potentialTeacher->hasRole(Constants::SUPER_ROLE)) {
                $instructors[] = $potentialTeacher;
            }
        }

        return $this->render("section/edit.html.twig", [
            "courses" => $courses,
            "users" => $users,
            "instructors" => $instructors,
            "section" => $section,
            "section_taker_roles" => $sectionTakerRoles,
            "section_teacher_roles" => $sectionTeacherRoles
        ]);
    }
    
    public function cloneSectionAction($sectionId, $name, $term, $year, $numberOfClones) {
        $user = $this->userService->getCurrentUser();
        if (!get_class($user)) {
            return $this->returnForbiddenResponse("YOU ARE NOT LOGGED IN");
        }

        $section = $this->sectionService->getSectionById($sectionId);
        if (!$section) {
            return $this->returnForbiddenResponse("SECTION ".$sectionId." DOES NOT EXIST");
        }

        $semester = $this->semesterService->getSemesterByTermAndYear($term, $year);
        if ($semester == NULL) {
            $semester = $this->semesterService->createSemesterByTermAndYear($term, $year);
            $this->semesterService->insertSemester($semester);
        }

        $teachesRole = $this->roleService->getRoleByRoleName(Constants::TEACHES_ROLE);
        for ($i = 1; $i <= $numberOfClones; $i++) {
            $newSection = clone $section;
            $newSection->semester = $semester;
            $newSection->name = $name."-".str_pad($i, 2, "0", STR_PAD_LEFT);
            $newSection->user_roles = [$this->userSectionRoleService->createUserSectionRole($user, $newSection, $teachesRole)];
            $this->sectionService->insertSection($newSection);
        }

        return $this->redirectToRoute("section_edit",
        [
            "sectionId" => $newSection->id
        ]);
    }

    public function deleteSectionAction($sectionId) {
        $user = $this->userService->getCurrentUser();
        if (!get_class($user)) {
            return $this->returnForbiddenResponse("YOU ARE NOT LOGGED IN");
        }
        if (!$user->hasRole(Constants::ADMIN_ROLE) && !$user->hasRole(Constants::SUPER_ROLE)) {
            return $this->returnForbiddenResponse("YOU ARE NOT ALLOWED TO DELETE THIS SECTION");
        }
        
        /* Get the section */
        if (!isset($sectionId) || !($sectionId > 0)) {
            return $this->returnForbiddenResponse("SECTION ID WAS NOT PROVIDED OR NOT FORMATTED PROPERLY");
        }
        
        $section = $this->sectionService->getSectionById($sectionId);
        if (!$section) {
            return $this->returnForbiddenResponse("SECTION ".$sectionId." DOES NOT EXIST");
        }

        $section->is_deleted = !$section->is_deleted;
        $section->master_id = null;
        $this->sectionService->insertSection($section);

        return $this->redirectToRoute("homepage");
    }

    public function modifyPostAction(Request $request) {
        /* Validate the current user */
        $user = $this->userService->getCurrentUser();
        if (!get_class($user)) {
            return $this->returnForbiddenResponse("YOU ARE NOT LOGGED IN");
        }
        
        /* See which fields were included */
        $postData = $request->request->all();
        
        /* Check mandatory fields */
        $sectionCourse = $postData["course"];
        $sectionName = $postData["name"];
        $sectionTerm = $postData["semester"];
        $sectionYear = $postData["year"];

        if (!isset($sectionName) || trim($sectionName) == "" || !isset($sectionCourse) || !isset($sectionTerm) || !isset($sectionYear)) {
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
        }

        /* Create new section */
        $sectionId = $postData["section"];
        if ($sectionId == 0) {
            /* Only super users and admins can make/edit a section */
            if (!$user->hasRole(Constants::SUPER_ROLE) && !$user->hasRole(Constants::ADMIN_ROLE)) {
                return $this->returnForbiddenResponse("YOU DO NOT HAVE PERMISSION TO MAKE A SECTION");
            }
            
            $section = $this->sectionService->createEmptySection();
        } else if (isset($sectionId) || $sectionId > 0) {
            $section = $this->sectionService->getSectionById($sectionId);
            
            if (!$section) {
                return $this->returnForbiddenResponse("SECTION ".$sectionId." DOES NOT EXIST");
            }
            
            /* Only super users and admins can make/edit a section */
            if(!($user->hasRole(Constants::SUPER_ROLE) || $user->hasRole(Constants::ADMIN_ROLE) || $this->graderService->isTeaching($user, $section))){
                return $this->returnForbiddenResponse("YOU DO NOT HAVE PERMISSION TO EDIT THIS SECTION");
            }
        } else {
            return $this->returnForbiddenResponse("SECTION ID WAS NOT PROVIDED OR FORMATTED PROPERLY");
        }
        
        /* Get the course */
        $courseId = $postData["course"];
        if (!isset($courseId) || !($courseId > 0)) {
            return $this->returnForbiddenResponse("COURSE ID WAS NOT PROVIDED OR NOT FORMATTED PROPERLY");
        }
        
        $course = $this->courseService->getCourseById($courseId);
        if (!$course) {
            return $this->returnForbiddenResponse("COURSE ".$courseId." DOES NOT EXIST");
        }
        
        /* Set the necessary fields*/
        $section->name = trim($sectionName);
        $section->course = $course;

        /*Validate the semester*/
        $semester = $this->semesterService->getSemesterByTermAndYear($sectionTerm, $sectionYear);
        if (!$semester){
            $semester = $this->semesterService->createSemesterByTermAndYear($sectionTerm, $sectionYear, false);
            $this->semesterService->insertSemester($semester);
        }
        $section->semester = $semester;
        
        /* See if the dates were provided or if we will do them automatically */
        $dates = $this->getDateTime($sectionTerm, $sectionYear);
        $sectionStartTime = $postData["start_time"];
        if (isset($sectionStartTime) && $sectionStartTime != "") {
            $customStartTime = DateTime::createFromFormat("m/d/Y H:i:s", $sectionStartTime." 00:00:00");
            
            if (!$customStartTime || $customStartTime->format("m/d/Y") != $sectionStartTime) {
                return $this->returnForbiddenResponse("PROVIDED INVALID START TIME ". $sectionStartTime);
            } else {
                $section->start_time = $customStartTime;
            }
        } else {
            $section->start_time = $dates[0];
        }
        
        $sectionEndTime = $postData["end_time"];
        if (isset($sectionEndTime) && $sectionEndTime != "") {
            $customEndTime = DateTime::createFromFormat("m/d/Y H:i:s", $sectionEndTime." 23:59:59");
            
            if (!$customEndTime || $customEndTime->format("m/d/Y") != $sectionEndTime) {
                return $this->returnForbiddenResponse("PROVIDED INVALID END TIME ". $sectionEndTime);
            } else {
                $section->end_time = $customEndTime;
            }
        } else {
            $section->end_time = $dates[1];
        }
        
        /* validate that the end time is after the start time */
        if ($section->end_time <= $section->start_time) {
            return $this->returnForbiddenResponse("THE END TIME MUST BE AFTER THE START TIME FOR THE SECTION");
        }
        
        /* Default these to false */
        $section->is_deleted = false;
        $section->is_public = false;
        
        $this->sectionService->insertSection($section);
        
        $sectionStudents = $postData["students"];
        /* Validate the students csv */
        $students = array_unique(json_decode($sectionStudents));
        
        foreach ($students as $student) {
            if (!filter_var($student, FILTER_VALIDATE_EMAIL)) {
                return $this->returnForbiddenResponse("PROVIDED STUDENT EMAIL ADDRESS ".$student." IS NOT VALID");
            }
            
            if (in_array($student, $teachers)) {
                return $this->returnForbiddenResponse("CANNOT ADD ".$student." AS A STUDENT BECAUSE THEY ARE ALREADY TAKING THIS SECTION");
            }
        }
        
        $sectionTeachers = $postData["teachers"];
        /* Validate teacher csv */
        $teachers = array_unique(json_decode($sectionTeachers));
        foreach ($teachers as $teacher) {
            if (!filter_var($teacher, FILTER_VALIDATE_EMAIL)) {
                return $this->returnForbiddenResponse("PROVIDED TEACHER EMAIL ADDRESS ".$teacher." IS NOT VALID");
            }
            
            if (in_array($teacher, $students)) {
                return $this->returnForbiddenResponse("CANNOT ADD " .$teacher . " AS A STUDENT BECAUSE THEY ARE ALREADY TEACHING THIS SECTION");
            }
        }
        
        $oldUsers = [];
        
        if ($sectionId == 0 && count(json_decode($sectionTeachers)) == 0) {
            /* Add the current user as a role */
            $role = $this->roleService->getRoleByRoleName(Constants::TEACHES_ROLE);
            $userSectionRole = $this->userSectionRoleService->createUserSectionRole($user, $section, $role);
            $entityManager->persist($userSectionRole);
            $this->userSectionRoleService->insertUserSectionRole($userSectionRole);
        } else if ($sectionId != 0) {
            foreach ($section->user_roles as $userRole) {
                $this->userSectionRoleService->deleteUserSectionRole($userRole);
                
                $oldUsers[$userRole->user->id] = $userRole->user;
            }
        }
        
        /* Add students from the students array */
        $takesRole = $this->roleService->getRoleByRoleName(Constants::TAKES_ROLE);
        foreach ($students as $student) {
            if (!filter_var($student, FILTER_VALIDATE_EMAIL)) {
                return $this->returnForbiddenResponse("PROVIDED STUDENT EMAIL ".$student." IS NOT VALID");
            }
            
            $studentUser = $this->userService->getUserByObject(["email" => $student]);
            if (!$studentUser) {
                $studentUser = $this->userService->createUser($student, $student);
                $this->userService->insertUser($studentUser);
            }
            
            $studentUserSectionRole = $this->userSectionRoleService->createUserSectionRole($studentUser, $section, $takesRole);
            $this->userSectionRoleService->insertUserSectionRole($studentUserSectionRole);
            
            unset($oldUsers[$studentUser->id]);
        }
        
        /* Add the teachers from the teachers array */
        
        $teachesRole = $this->roleService->getRoleByRoleName(Constants::TEACHES_ROLE);

        foreach ($teachers as $teacher) {
            if (!filter_var($teacher, FILTER_VALIDATE_EMAIL)) {
                return $this->returnForbiddenResponse("PROVIDED TEACHER EMAIL ADDRESS ".$teacher." IS NOT VALID");
            }
            
            $teacherUser = $this->userService->getUserByObject(["email" => $teacher]);
            
            if (!$teacherUser) {
                return $this->returnForbiddenResponse("TEACHER WITH EMAIL ".$teacher." DOES NOT EXIST");
            }
            
            if ($this->graderService->isTaking($teacherUser, $section)) {
                return $this->returnForbiddenResponse($student . " IS ALREADY TEACHING THIS COURSE");
            }

            $teacherUserSectionRole = $this->userSectionRoleService->createUserSectionRole($teacherUser, $section, $teachesRole);
            $this->userSectionRoleService->insertUserSectionRole($teacherUserSectionRole);
            
            unset($oldUsers[$studentUser->id]);
        }
        
        foreach ($oldUsers as $oldUser) {
            foreach ($section->assignments as $assignments) {
                foreach ($assignments->teams as &$team) {
                    $team->users->removeElement($oldUser);

                    if ($team->users->count() == 0) {
                        $this->teamService->deleteTeam($team);
                    } else {
                        $this->teamService->insertTeam($team);
                    }
                }
            }
        }

        if ($section->is_master && !$section->course->is_contest && count($section->slaves) > 0) {
            foreach ($section->slaves as $sectionSlave) {
                /* Assignments */
                foreach ($section->assignments as $masterAssignment) {
                    $isInSlave = false;
                    $masterAssignmentToClone = clone $masterAssignment;
                    foreach ($sectionSlave->assignments as $slaveAssignment) {
                        if ($slaveAssignment->name == $masterAssignment->name) {
                            $isInSlave = true;
                            break;
                        }
                    }
                    if ($isInSlave) {
                        foreach ($slaveAssignment->problems as $slaveProblem) {
                            $this->problemService->deleteProblem($slaveProblem, false);
                        }
                        $this->assignmentService->deleteAssignment($slaveAssignment, false);
                    }

                    $masterAssignmentToClone->section = $sectionSlave;
                    $this->assignmentService->insertAssignment($masterAssignmentToClone);
                }

                /* Course */
                $sectionSlave->course = $section->course;

                $this->sectionService->insertSection($sectionSlave);
            }
        }

        /* Redirect to the section page */
        $url = $this->generateUrl("section", ["sectionId" => $section->id]);

        $response = new Response(json_encode(["redirect_url" => $url]));
        return $this->returnOkResponse($response);
    }

    //switch to using semester object
    private function getDateTime($semester, $year){
        switch ($semester) {
            case "Fall":
                return [DateTime::createFromFormat("m/d/Y H:i:s", "08/01/".$year." 00:00:00"),
                        DateTime::createFromFormat("m/d/Y H:i:s", "12/31/".$year." 23:59:59")];
            case "Spring":
                return [DateTime::createFromFormat("m/d/Y H:i:s", "01/01/".$year." 00:00:00"),
                        DateTime::createFromFormat("m/d/Y H:i:s", "05/31/".$year." 23:59:59")];
            default:
                return [DateTime::createFromFormat("m/d/Y H:i:s", "05/01/".$year." 00:00:00"),
                        DateTime::createFromFormat("m/d/Y H:i:s", "08/31/".$year." 23:59:59")];	
        }
    }

    public function searchSubmissionsAction(Request $request) {
        /* Validate the current user */
        $user = $this->userService->getCurrentUser();
        if (!get_class($user)) {
            return $this->returnForbiddenResponse("YOU ARE NOT LOGGED IN");
        }

        $isElevatedUser = $user->hasRole(Constants::SUPER_ROLE) || $user->hasRole(Constnts::ADMIN_ROLE);

        /* See which fields were included */
        $postData = $request->request->all();
        $searchValue = $postData["val"];
        if (!isset($searchValue) || trim($searchValue) == "") {
            return $this->returnForbiddenResponse("SEARCH VALUE MUST BE PROVIDED FOR SEARCHING");			
        }

        $sectionId = $postData["id"];
        if (!isset($sectionId) || trim($sectionId) == "") {
            return $this->returnForbiddenResponse("SECTION ID MUST BE PROVIDED FOR SEARCHING");			
        }

        /* Validation */
        $searchValues = explode(";", $searchValue);
        $section = $this->sectionService->getSectionById($sectionId);
        
        if (!($this->graderService->isTeaching($user, $section) || 
              $this->graderService->isTaking($user, $section) || 
              $this->graderService->isHelping($user, $section) || 
              $isElevatedUser)) {
            return $this->returnForbiddenResponse("YOU ARE NOT ALLOWED THE SEARCH THE SUBMISSIONS OF THIS SECTION");
        }

        $elevatedQuery = "";
        if ($isElevatedUser) {
            $elevatedQuery = " OR 1=1";
        }

        $userTeams = $this->teamService->getTeamsForSectionSearch($user);
        $submissionDataQuery = $this->submissionService->getSubmissionSearchDataQuery($section->getAllProblems(), $userTeams, $elevatedQuery);

        $results = [];
        foreach ($searchValues as $searchVal) {
            $searchVal = trim($searchVal);
            $paginator = new Paginator($submissionDataQuery, true);

            foreach ($paginator as $paginatedSubmission) {
                if ($paginatedSubmission->id == $searchVal || 
                    stripos($paginatedSubmission->problem->assignment->name, $searchVal) !== false ||
                    stripos($paginatedSubmission->problem->name, $searchVal) !== false ||
                    stripos($paginatedSubmission->user->getFullName(), $searchVal) !== false ||
                    stripos($paginatedSubmission->user->getEmail(), $searchVal) !== false ||
                    $paginatedSubmission->isCorrect() && stripos("Correct", $searchVal) !== false) {
                    $results[] = $paginatedSubmission;
                    continue;				
                } else if (stripos("Correct", $searchVal) !== false) {
                    continue;
                } else if (!$paginatedSubmission->isCorrect() && stripos($paginatedSubmission->getResultString(), $searchVal) !== false) {
                    $results[] = $paginatedSubmission;
                    continue;
                }	

                $teamName = $paginatedSubmission->team->name;
                foreach ($paginatedSubmission->team->users as $teamUser) {
                    $teamName .= " ".$teamUser->getFullName()." ".$teamUser->getEmail();
                }
                
                if (stripos($teamName, $searchVal) !== false) {
                    $results[] = $paginatedSubmission;
                    continue;	
                }
            }
        }

        $response = new Response(json_encode([
            "results" => $results,		
        ]));
        return $this->returnOkResponse($response);
    }	

    private function logError($message) {
        $errorMessage = "SectionController: ".$message;
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
