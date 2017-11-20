<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Role;
use AppBundle\Entity\User;
use AppBundle\Entity\Team;
use AppBundle\Entity\Course;
use AppBundle\Entity\Section;
use AppBundle\Entity\Assignment;
use AppBundle\Entity\Problem;
use AppBundle\Entity\ProblemLanguage;
use AppBundle\Entity\UserSectionRole;
use AppBundle\Entity\Testcase;
use AppBundle\Entity\Submission;
use AppBundle\Entity\Language;
use AppBundle\Entity\AssignmentGradingMethod;
use AppBundle\Entity\Feedback;
use AppBundle\Entity\TestcaseResult;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class Fixtures extends Fixture {
	
    public function load(ObjectManager $manager) {
			
		$folder_path = "src/AppBundle/DataFixtures/ORM/";
		$date_format = "H:i:s m/d/Y";
		
		# ROLE Testing
		{
			$helps_role = new Role("Helps", "helps with the class - usually a TA, lab assistant, or grader");
			$manager->persist($helps_role);
			
			$takes_role = new Role("Takes", "takes the class - is taking the class as a student");
			$manager->persist($takes_role);
			
			$teach_role = new Role("Teaches", "teaches the class - is allowed to create and edit assignments and see student submissions");
			$manager->persist($teach_role);
			
			$judge_role = new Role("Judges", "judges the contest - similar to teaches role but for a contest");
			$manager->persist($judge_role);
		}
		
		# USER Testing
		{
			$prof_gallagher = new User("gallaghd@cedarville.edu", "gallaghd@cedarville.edu");
			$prof_gallagher->addRole("ROLE_SUPER");			
			$prof_gallagher->addRole("ROLE_ADMIN");
			$prof_gallagher->setFirstName("David");
			$prof_gallagher->setLastName("Gallagher");
			$manager->persist($prof_gallagher);

			$brauns_user = new User("cbrauns@cedarville.edu", "cbrauns@cedarville.edu");
			$brauns_user->addRole("ROLE_SUPER");			
			$brauns_user->addRole("ROLE_ADMIN");
			$brauns_user->setFirstName("Christopher");
			$brauns_user->setLastName("Brauns");
			$manager->persist($brauns_user);			
			
			$wolf_user = new User("ewolf@cedarville.edu", "ewolf@cedarville.edu");
			$wolf_user->addRole("ROLE_SUPER");			
			$wolf_user->addRole("ROLE_ADMIN");
			$wolf_user->setFirstName("Emily");
			$wolf_user->setLastName("Wolf");
			$manager->persist($wolf_user);
			
			$budd_user = new User("ebudd@cedarville.edu", "ebudd@cedarville.edu");
			$budd_user->addRole("ROLE_SUPER");			
			$budd_user->addRole("ROLE_ADMIN");
			$budd_user->setFirstName("Emmett");
			$budd_user->setLastName("Budd");
			$manager->persist($budd_user);
			
			$smith_user = new User("timothyglensmith@cedarville.edu", "timothyglensmith@cedarville.edu");
			$smith_user->addRole("ROLE_SUPER");			
			$smith_user->addRole("ROLE_ADMIN");			
			$smith_user->setFirstName("Timothy");
			$smith_user->setLastName("Smith");
			$manager->persist($smith_user);
		}
		
		# COURSE Testing
		{
			$course1 = new Course("CS-1210", "C++ Programming", "A class where you learn how to program", false, false, false);
			$manager->persist($course1);

			$course2 = new Course("CS-1210", "Object-Oriented Design", "A class where you learn how to do the OOD", false, false, false);
			$manager->persist($course2);
			
			$contest = new Course("", "Cedarville University Programming Contest", "The annual programming contest open to all majors and walks of life", true, true, false);
			$manager->persist($contest);
			
			$oldcourse = new Course("OLD-1234", "Old Course", "A pretend course that takes place in previous semesters", false, false, false);
			$manager->persist($oldcourse);
		}
		
		# SECTION Testing
		{
			$CS1210_01 = new Section($course1, "CS-1210-01", "Fall", 2017, \DateTime::createFromFormat($date_format, "00:00:00 08/21/2017"), \DateTime::createFromFormat($date_format, "23:59:59 12/16/2017"), false, false);
			
			
			$oldsection = new Section($oldcourse, "OLD-1234-01", "Spring", 2017, \DateTime::createFromFormat($date_format, "00:00:00 01/10/2017"), \DateTime::createFromFormat($date_format, "23:59:59 05/04/2017"), false, false);
			
			$contest_2017 = new Section($contest, "Local Contest Fall 2017", "Fall", 2017, 
									\DateTime::createFromFormat($date_format, "00:00:00 11/01/2017"), 
									\DateTime::createFromFormat($date_format, "23:59:59 11/01/2017"), false, false);

			$manager->persist($CS1210_01);
			$manager->persist($oldsection);
			$manager->persist($contest_2017);
		}
		
		# USERSECTIONROLE Testing
		{
			$manager->persist(new UserSectionRole($wolf_user, $CS1210_01, $takes_role));
			$manager->persist(new UserSectionRole($budd_user, $CS1210_01, $takes_role));
			$manager->persist(new UserSectionRole($prof_gallagher, $CS1210_01, $takes_role));
			$manager->persist(new UserSectionRole($brauns_user, $CS1210_01, $takes_role));
			$manager->persist(new UserSectionRole($smith_user, $CS1210_01, $teach_role));
			
			$manager->persist(new UserSectionRole($wolf_user, $oldsection, $takes_role));
			$manager->persist(new UserSectionRole($budd_user, $oldsection, $takes_role));
			$manager->persist(new UserSectionRole($prof_gallagher, $oldsection, $takes_role));
			$manager->persist(new UserSectionRole($brauns_user, $oldsection, $takes_role));
			$manager->persist(new UserSectionRole($smith_user, $oldsection, $teach_role));
			
			$manager->persist(new UserSectionRole($wolf_user, $contest_2017, $takes_role));
			$manager->persist(new UserSectionRole($budd_user, $contest_2017, $takes_role));
			$manager->persist(new UserSectionRole($smith_user, $contest_2017, $takes_role));
			$manager->persist(new UserSectionRole($prof_gallagher, $contest_2017, $teach_role));
			$manager->persist(new UserSectionRole($brauns_user, $contest_2017, $teach_role));
		}
		
		# ASSIGNMENTGRADINGMETHOD Testing
		{
			$assignment_grdmethod0 = new AssignmentGradingMethod(0.00);
			$assignment_grdmethod1 = new AssignmentGradingMethod(0.10);	
			$assignment_grdmethod2 = new AssignmentGradingMethod(0.20);		

			$manager->persist($assignment_grdmethod0);
			$manager->persist($assignment_grdmethod1);
			$manager->persist($assignment_grdmethod2);
		}
		
		# ASSIGNMENT Testing
		{
			
			$assignment_04 = new Assignment($CS1210_01, 
									"Homework #2", "This is the second homework assignment", 
									\DateTime::createFromFormat($date_format, "00:00:00 10/30/2017"), 
									\DateTime::createFromFormat($date_format, "23:59:59 11/30/2017"), 
									\DateTime::createFromFormat($date_format, "23:59:59 12/15/2017"), 1, $assignment_grdmethod0, false);
			$manager->persist($assignment_04);
			
			
			$assignment_02 = new Assignment($contest_2017, 
									"Practice Contest", "The is the practice contest before the actual contest", 
									\DateTime::createFromFormat($date_format, "13:00:00 10/30/2017"), 
									\DateTime::createFromFormat($date_format, "17:00:00 11/30/2017"), 
									\DateTime::createFromFormat($date_format, "17:00:00 12/15/2017"), 1, $assignment_grdmethod1, false);
			$manager->persist($assignment_02);
			
			$assignment_03 = new Assignment($contest_2017, 
									"Actual Contest", "The is the contest", 
									\DateTime::createFromFormat($date_format, "13:00:00 10/30/2017"), 
									\DateTime::createFromFormat($date_format, "17:00:00 11/30/2017"), 
									\DateTime::createFromFormat($date_format, "17:00:00 12/15/2017"), 1, $assignment_grdmethod2, false);
			$manager->persist($assignment_03);
			
			$assignment_01 = new Assignment($CS1210_01, 
									"Homework #1", "This is the first homework assignment", 
									\DateTime::createFromFormat($date_format, "00:00:00 10/30/2017"), 
									\DateTime::createFromFormat($date_format, "23:59:59 11/01/2017"), 
									\DateTime::createFromFormat($date_format, "23:59:59 12/15/2017"), 1, $assignment_grdmethod0, false);
			$manager->persist($assignment_01);
			
			$old_assignment_01 = new Assignment($oldsection, 
									"Old #1", "This is the old homework assignment", 
									\DateTime::createFromFormat($date_format, "00:00:00 01/11/2017"), 
									\DateTime::createFromFormat($date_format, "23:59:59 01/30/2017"), 
									\DateTime::createFromFormat($date_format, "23:59:59 05/04/2017"), 1, $assignment_grdmethod0, false);
			$manager->persist($old_assignment_01);
		}
		
		# TEAM Testing
		# make some teams for one of the classes
		{
			# ASSIGNMENT 1
			$team_01_1 = new Team("Everyone", $assignment_01);					
			$team_01_1->users[] = $wolf_user;
			$team_01_1->users[] = $budd_user;
			$team_01_1->users[] = $brauns_user;
			$team_01_1->users[] = $prof_gallagher;
			$manager->persist($team_01_1);			
			
			# ASSIGNMENT 4
			$team_04_1 = new Team("Wolf_01", $assignment_04);
			$team_04_1->users[] = $wolf_user;
			
			$team_04_2 = new Team("Budd_01", $assignment_04);	
			$team_04_2->users[] = $budd_user;
			
			$team_04_3 = new Team("Gallagher_01", $assignment_04);
			$team_04_3->users[] = $prof_gallagher;
			
			$team_04_4 = new Team("Brauns_01", $assignment_04);
			$team_04_4->users[] = $brauns_user;
			
			$manager->persist($team_04_1);
			$manager->persist($team_04_2);
			$manager->persist($team_04_3);
			$manager->persist($team_04_4);
			
			# ASSIGNMENT 2
			$team_02_1 = new Team("Smith_01", $assignment_02);
			$team_02_1->users[] = $smith_user;
			
			$team_02_2 = new Team("Wolf_01", $assignment_02);
			$team_02_2->users[] = $wolf_user;
			
			$team_02_3 = new Team("Budd_01", $assignment_02);
			$team_02_3->users[] = $budd_user;
			
			$manager->persist($team_02_1);
			$manager->persist($team_02_2);
			$manager->persist($team_02_3);
			
			# ASSIGNMENT 3
			$team_03_1 = new Team("Smith_01", $assignment_03);
			$team_03_1->users[] = $smith_user;
			
			$team_03_2 = new Team("Wolf_01", $assignment_03);
			$team_03_2->users[] = $wolf_user;
			
			$team_03_3 = new Team("Budd_01", $assignment_03);
			$team_03_3->users[] = $budd_user;
			
			$manager->persist($team_03_1);
			$manager->persist($team_03_2);
			$manager->persist($team_03_3);
			
			# OLD ASSIGNMENT 1
			$team_50 = new Team("Everyone", $old_assignment_01);					
			$team_50->users[] = $wolf_user;
			$team_50->users[] = $budd_user;
			$team_50->users[] = $prof_gallagher;
			$team_50->users[] = $brauns_user;
			$manager->persist($team_50);
		}
		
		# LANGUAGE Testing
		{
			$def_c = fopen($folder_path."default_code/default.c", "r") or die("Unable to open default.c");
			$def_cpp = fopen($folder_path."default_code/default.cpp", "r") or die("Unable to open default.cpp");
			$def_java = fopen($folder_path."default_code/default.java", "r") or die("Unable to open default.java");
			
			$language_C = new Language("C", ".c", "c_cpp", $def_c);	
			$language_CPP = new Language("C++", ".cpp", "c_cpp", $def_cpp);
			$language_JAVA = new Language("Java", ".java", "java", $def_java);
			
			$manager->persist($language_C);
			$manager->persist($language_CPP);
			$manager->persist($language_JAVA);			
		}
		
		# PROBLEM Testing
		{
			$problems = [];
			
			// put new testcases in a folder and map the problem to the name here
			// if the new problem has the same name as an old problem, change it
			$prob_folds = [];
			
			# HOMEWORK 1 For CS-1210-01
			$desc_file_01 = fopen($folder_path."sum/description.txt", "r") or die("Unable to open 1.desc");
			$problem_01 = new Problem($assignment_04, "Calculate the Sum", $desc_file_01, 1, 1000, false, 0, 0, 0);
			$problems[] = $problem_01;
			$prob_folds[$problem_01->name] = "sum";
			
			$desc_file_02 = fopen($folder_path."diff/description.txt", "r") or die("Unable to open 2.desc");
			$problem_02 = new Problem($assignment_04, "Calculate the Difference", $desc_file_02, 1, 1000, false, 0, 0, 0);
			$problems[] = $problem_02;
			$prob_folds[$problem_02->name] = "diff";
			
			$desc_file_03 = fopen($folder_path."prod/description.txt", "r") or die("Unable to open 3.desc");
			$problem_03 = new Problem($assignment_04, "Calculate the Product", $desc_file_03, 1, 1000, false, 0, 0, 0);
			$problems[] = $problem_03;
			$prob_folds[$problem_03->name] = "prod";
			
			$desc_file_04 = fopen($folder_path."quot/description.txt", "r") or die("Unable to open 4.desc");
			$problem_04 = new Problem($assignment_04, "Calculate the Quotient", $desc_file_04, 1, 1000, false, 0, 0, 0);
			$problems[] = $problem_04;
			$prob_folds[$problem_04->name] = "quot";
			
			
			# HOMEWORK 2 For CS-1210-01
			$desc_file_11 = fopen($folder_path."sum/description.txt", "r") or die("Unable to open 1.desc");
			$problem_11 = new Problem($assignment_01, "Get the Sum", $desc_file_11, 2, 1000, false, 0, 0, 0);
			$problems[] = $problem_11;
			$prob_folds[$problem_11->name] = "sum";
			
			$desc_file_12 = fopen($folder_path."diff/description.txt", "r") or die("Unable to open 2.desc");
			$problem_12 = new Problem($assignment_01, "Get the Difference", $desc_file_12, 2, 1000, false, 0, 0, 0);
			$problems[] = $problem_12;
			$prob_folds[$problem_12->name] = "diff";
			
			$desc_file_13 = fopen($folder_path."prod/description.txt", "r") or die("Unable to open 3.desc");
			$problem_13 = new Problem($assignment_01, "Get the Product", $desc_file_13, 2, 1000, false, 0, 0, 0);
			$problems[] = $problem_13;
			$prob_folds[$problem_13->name] = "prod";
			
			$desc_file_14 = fopen($folder_path."quot/description.txt", "r") or die("Unable to open 4.desc");
			$problem_14 = new Problem($assignment_01, "Get the Quotient", $desc_file_14, 2, 1000, false, 0, 0, 0);
			$problems[] = $problem_14;
			$prob_folds[$problem_14->name] = "quot";
			
			
			# PRACTICE CONTEST
			$desc_file_05 = fopen($folder_path."Z/description.txt", "r") or die("Unable to open 5.desc");
			$problem_05 = new Problem($assignment_02, "Z - Happy Trails", $desc_file_05, 1, 1000, false, 10, 5, .10);
			$problems[] = $problem_05;
			$prob_folds[$problem_05->name] = "Z";
			
			# ACTUAL CONTEST
			$desc_file_06 = fopen($folder_path."A/description.txt", "r") or die("Unable to open 6.desc");
			$problem_06 = new Problem($assignment_03, "A - The Key to Cryptography", $desc_file_06, 1, 1000, false, 10, 5, .10);
			$problems[] = $problem_06;
			$prob_folds[$problem_06->name] = "A";
			
			$desc_file_07 = fopen($folder_path."B/description.txt", "r") or die("Unable to open 7.desc");
			$problem_07 = new Problem($assignment_03, "B - Red Rover", $desc_file_07, 2, 1000, false, 10, 5, .10);
			$problems[] = $problem_07;
			$prob_folds[$problem_07->name] = "B";
			
			$desc_file_08 = fopen($folder_path."C/description.txt", "r") or die("Unable to open 8.desc");
			$problem_08 = new Problem($assignment_03, "C - Lost In Translation", $desc_file_08, 3, 1000, false, 10, 5, .10);
			$problems[] = $problem_08;
			$prob_folds[$problem_08->name] = "C";
			
			# HOMEWORK OLD For OLD-1234-01
			$desc_file_old1 = fopen($folder_path."sum/description.txt", "r") or die("Unable to open old.desc");
			$problem_old1 = new Problem($old_assignment_01, "Old the Sum", $desc_file_old1, 1, 1000, false, 10, 5, .10);
			$problems[] = $problem_old1;
			$prob_folds[$problem_old1->name] = "sum";
			
			$desc_file_old2 = fopen($folder_path."diff/description.txt", "r") or die("Unable to open 2.desc");
			$problem_old2 = new Problem($old_assignment_01, "Old the Difference", $desc_file_old2, 1, 1000, false, 10, 5, .10);
			$problems[] = $problem_old2;
			$prob_folds[$problem_old2->name] = "diff";
			
			$desc_file_old3 = fopen($folder_path."prod/description.txt", "r") or die("Unable to open 3.desc");
			$problem_old3 = new Problem($old_assignment_01, "Old the Product", $desc_file_old3, 1, 1000, false, 10, 5, .10);
			$problems[] = $problem_old3;
			$prob_folds[$problem_old3->name] = "prod";
			
			$desc_file_old4 = fopen($folder_path."quot/description.txt", "r") or die("Unable to open 4.desc");
			$problem_old4 = new Problem($old_assignment_01, "Old the Quotient", $desc_file_old4, 1, 1000, false, 10, 5, .10);
			$problems[] = $problem_old4;
			$prob_folds[$problem_old4->name] = "quot";
			
			$manager->persist($problem_01);		
			$manager->persist($problem_02);
			$manager->persist($problem_03);		
			$manager->persist($problem_04);
			$manager->persist($problem_05);
			$manager->persist($problem_06);
			$manager->persist($problem_07);
			$manager->persist($problem_08);
			$manager->persist($problem_11);		
			$manager->persist($problem_12);
			$manager->persist($problem_13);		
			$manager->persist($problem_14);
			
			
			$manager->persist($problem_old1);		
			$manager->persist($problem_old2);
			$manager->persist($problem_old3);		
			$manager->persist($problem_old4);
		}
		
		# PROBLEM LANGUAGE Testing
		{						
			foreach($problems as $prob){
				
				$problang_01 = new ProblemLanguage($language_CPP, $prob, NULL, NULL);			
				$problang_02 = new ProblemLanguage($language_C, $prob, NULL, NULL);
				$problang_03 = new ProblemLanguage($language_JAVA, $prob, NULL, NULL);
				
				$manager->persist($problang_01);
				$manager->persist($problang_02);
				$manager->persist($problang_03);				
			}
			
		}
		
		
		# FEEDBACK Testing
		{
			$short_file_01 = fopen($folder_path."1.short", "r") or die("Unable to open 1.short!");			
			$long_file_01 = fopen($folder_path."1.long", "r") or die("Unable to open 1.long!");
			
			$feedback_general = new Feedback($short_file_01, $long_file_01);
			$feedback_negatives = new Feedback($short_file_01, $long_file_01);
			
			$manager->persist($feedback_general);		
			$manager->persist($feedback_negatives);		
		}
		
		# TESTCASES for PROBLEMS
		{
			foreach($problems as $prob){			
				
				$prob_name = $prob_folds[$prob->name];
				
				$in_file_01 = fopen($folder_path.$prob_name."/1.in", "r") or die("Unable to open 1.in!");
				$in_file_02 = fopen($folder_path.$prob_name."/2.in", "r") or die("Unable to open 2.in!");
				$in_file_03 = fopen($folder_path.$prob_name."/3.in", "r") or die("Unable to open 3.in!");
				$in_file_04 = fopen($folder_path.$prob_name."/4.in", "r") or die("Unable to open 4.in!");
				
				$out_file_01 = fopen($folder_path.$prob_name."/1.out", "r") or die("Unable to open 1.out!");
				$out_file_02 = fopen($folder_path.$prob_name."/2.out", "r") or die("Unable to open 2.out!");
				$out_file_03 = fopen($folder_path.$prob_name."/3.out", "r") or die("Unable to open 3.out!");
				$out_file_04 = fopen($folder_path.$prob_name."/4.out", "r") or die("Unable to open 4.out!");
							
				$testcase_01 = new Testcase($prob, 1, $in_file_01, $out_file_01, $feedback_general, 1, false);
				$testcase_02 = new Testcase($prob, 2, $in_file_02, $out_file_02, $feedback_general, 2, false);
				$testcase_03 = new Testcase($prob, 3, $in_file_03, $out_file_03, $feedback_negatives, 3, false);
				$testcase_04 = new Testcase($prob, 4, $in_file_04, $out_file_04, $feedback_negatives, 6, false);
			
				$manager->persist($testcase_01);
				$manager->persist($testcase_02);	
				$manager->persist($testcase_03);	
				$manager->persist($testcase_04);
			}
		}
			
		$manager->flush();
	}
}

?>
