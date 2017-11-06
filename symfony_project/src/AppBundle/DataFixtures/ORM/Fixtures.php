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
use AppBundle\Entity\ProblemGradingMethod;
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

			$prof_brauns = new User("cbrauns@cedarville.edu", "cbrauns@cedarville.edu");
			$prof_brauns->addRole("ROLE_SUPER");			
			$prof_brauns->addRole("ROLE_ADMIN");
			$prof_brauns->setFirstName("Christopher");
			$prof_brauns->setLastName("Brauns");
			$manager->persist($prof_brauns);			
			
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
			
			$annie_user = new User("amathis11@gmail.com", "amathis11@gmail.com");
			$annie_user->setFirstName("Annie");
			$annie_user->setLastName("Mathis");
			$manager->persist($annie_user);
		}
		
		# COURSE Testing
		{
			$course1 = new Course("CS-1210", "C++ Programming", "A class where you learn how to program", false, false, false);
			$manager->persist($course1);

			$course2 = new Course("CS-1210", "Object-Oriented Design", "A class where you learn how to do the OOD", false, false, false);
			$manager->persist($course2);
			
			$contest = new Course("", "Cedarville University Programming Contest", "The annual programming contest open to all majors and walks of life", true, true, false);
			$manager->persist($contest);
		}
		
		# SECTION Testing
		{
			$CS1210_01 = new Section($course1, "CS-1210-01", "Fall", 2017, \DateTime::createFromFormat($date_format, "00:00:00 08/21/2017"), \DateTime::createFromFormat($date_format, "23:59:59 12/16/2017"), false, false);
			//$CS1220_01 = new Section($course2, "CS-1220-01", "Fall", 2017, \DateTime::createFromFormat($date_format, "00:00:00 08/21/2017"), \DateTime::createFromFormat($date_format, "23:59:59 12/16/2017"), false, false);
			
			$contest_2017 = new Section($contest, "Local Contest Fall 2017", "Fall", 2017, 
									\DateTime::createFromFormat($date_format, "00:00:00 11/01/2017"), 
									\DateTime::createFromFormat($date_format, "23:59:59 11/01/2017"), false, false);

			$manager->persist($CS1210_01);
			//$manager->persist($CS1220_01);
			$manager->persist($contest_2017);
		}
		
		# USERSECTIONROLE Testing
		{
			$manager->persist(new UserSectionRole($wolf_user, $CS1210_01, $takes_role));
			$manager->persist(new UserSectionRole($budd_user, $CS1210_01, $takes_role));
			$manager->persist(new UserSectionRole($prof_gallagher, $CS1210_01, $takes_role));
			$manager->persist(new UserSectionRole($prof_brauns, $CS1210_01, $takes_role));
			$manager->persist(new UserSectionRole($annie_user, $CS1210_01, $takes_role));
			$manager->persist(new UserSectionRole($smith_user, $CS1210_01, $teach_role));
			
			//$manager->persist(new UserSectionRole($prof_gallagher, $CS1220_01, $takes_role));
			//$manager->persist(new UserSectionRole($prof_brauns, $CS1220_01, $takes_role));
			//$manager->persist(new UserSectionRole($smith_user, $CS1220_01, $teach_role));
			
			$manager->persist(new UserSectionRole($wolf_user, $contest_2017, $takes_role));
			$manager->persist(new UserSectionRole($budd_user, $contest_2017, $takes_role));
			$manager->persist(new UserSectionRole($smith_user, $contest_2017, $takes_role));
			$manager->persist(new UserSectionRole($prof_gallagher, $contest_2017, $teach_role));
			$manager->persist(new UserSectionRole($prof_brauns, $contest_2017, $teach_role));
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
			$assignment_01 = new Assignment($CS1210_01, 
									"Homework #1", "This is the first homework assignment", 
									\DateTime::createFromFormat($date_format, "00:00:00 10/30/2017"), 
									\DateTime::createFromFormat($date_format, "23:59:59 11/30/2017"), 
									\DateTime::createFromFormat($date_format, "08:00:00 12/15/2017"), 0.0, $assignment_grdmethod0, false);
			$manager->persist($assignment_01);
			
			
			$assignment_02 = new Assignment($contest_2017, 
									"Practice Contest", "The is the practice contest before the actual contest", 
									\DateTime::createFromFormat($date_format, "13:00:00 10/30/2017"), 
									\DateTime::createFromFormat($date_format, "17:00:00 11/30/2017"), 
									\DateTime::createFromFormat($date_format, "17:00:00 12/15/2017"), 0.0, $assignment_grdmethod1, false);
			$manager->persist($assignment_02);
			
			$assignment_03 = new Assignment($contest_2017, 
									"Actual Contest", "The is the contest", 
									\DateTime::createFromFormat($date_format, "13:00:00 10/30/2017"), 
									\DateTime::createFromFormat($date_format, "17:00:00 11/30/2017"), 
									\DateTime::createFromFormat($date_format, "17:00:00 12/15/2017"), 0.0, $assignment_grdmethod2, false);
			$manager->persist($assignment_03);
		}
		
		# TEAM Testing
		# make some teams for one of the classes
		{
			$team_01 = new Team("Wolf_01", $assignment_01);
			$team_02 = new Team("Budd_01", $assignment_01);	
			$team_03 = new Team("Gallagher_01", $assignment_01);
			$team_20 = new Team("Mathis_01", $assignment_01);	
			$team_21 = new Team("Brauns_01", $assignment_01);
					
			$team_01->users[] = $wolf_user;
			$team_02->users[] = $budd_user;
			$team_03->users[] = $prof_gallagher;
			$team_20->users[] = $annie_user;
			$team_21->users[] = $prof_brauns;
			
			
			$team_04 = new Team("Smith_01", $assignment_02);
			$team_04->users[] = $smith_user;
			
			$team_05 = new Team("Wolf_01", $assignment_02);
			$team_05->users[] = $wolf_user;
			
			$team_06 = new Team("Budd_01", $assignment_02);
			$team_06->users[] = $budd_user;
			
			
			$team_08 = new Team("Smith_01", $assignment_03);
			$team_08->users[] = $smith_user;
			
			$team_09 = new Team("Wolf_01", $assignment_03);
			$team_09->users[] = $wolf_user;
			
			$team_10 = new Team("Budd_01", $assignment_03);
			$team_10->users[] = $budd_user;

		
			$manager->persist($team_01);
			$manager->persist($team_02);
			$manager->persist($team_03);
			$manager->persist($team_04);
			$manager->persist($team_05);
			$manager->persist($team_06);
			$manager->persist($team_08);
			$manager->persist($team_09);
			$manager->persist($team_10);
			$manager->persist($team_20);
			$manager->persist($team_21);
		}
		
		# LANGUAGE Testing
		{
			$language_C = new Language("C");	
			$language_CPP = new Language("C++");
			$language_JAVA = new Language("Java");
			
			$manager->persist($language_C);
			$manager->persist($language_CPP);
			$manager->persist($language_JAVA);			
		}
		
		# PROBLEMGRADINGMETHOD Testing
		{
			$prob_grdmethod00 = new ProblemGradingMethod(0, 0, 0);
			$prob_grdmethod10 = new ProblemGradingMethod(10, 10, .10);
			$prob_grdmethod01 = new ProblemGradingMethod(10, 1, .10);
			
			$manager->persist($prob_grdmethod00);
			$manager->persist($prob_grdmethod10);
			$manager->persist($prob_grdmethod01);
		}
		
		# PROBLEM Testing
		{
			$problems = [];
			
			// put new testcases in a folder and map the problem to the name here
			// if the new problem has the same name as an old problem, change it
			$prob_folds = [];
			
			# HOMEWORK 1 For CS-1210-01
			$desc_file_01 = fopen($folder_path."sum/description.txt", "r") or die("Unable to open 1.desc");
			$problem_01 = new Problem($assignment_01, "Calculate the Sum", $desc_file_01, 0.0, $prob_grdmethod00, 1000, false);
			$problems[] = $problem_01;
			$prob_folds[$problem_01->name] = "sum";
			
			$desc_file_02 = fopen($folder_path."diff/description.txt", "r") or die("Unable to open 2.desc");
			$problem_02 = new Problem($assignment_01, "Calculate the Difference", $desc_file_02, 0.0, $prob_grdmethod00, 1000, false);
			$problems[] = $problem_02;
			$prob_folds[$problem_02->name] = "diff";
			
			$desc_file_03 = fopen($folder_path."prod/description.txt", "r") or die("Unable to open 3.desc");
			$problem_03 = new Problem($assignment_01, "Calculate the Product", $desc_file_03, 0.0, $prob_grdmethod00, 1000, false);
			$problems[] = $problem_03;
			$prob_folds[$problem_03->name] = "prod";
			
			$desc_file_04 = fopen($folder_path."quot/description.txt", "r") or die("Unable to open 4.desc");
			$problem_04 = new Problem($assignment_01, "Calculate the Quotient", $desc_file_04, 0.0, $prob_grdmethod00, 1000, false);
			$problems[] = $problem_04;
			$prob_folds[$problem_04->name] = "quot";
			
			# PRACTICE CONTEST
			$desc_file_05 = fopen($folder_path."Z/description.txt", "r") or die("Unable to open 5.desc");
			$problem_05 = new Problem($assignment_02, "Z - Happy Trails", $desc_file_05, 0.0, $prob_grdmethod10, 1000, false);
			$problems[] = $problem_05;
			$prob_folds[$problem_05->name] = "Z";
			
			# ACTUAL CONTEST
			$desc_file_06 = fopen($folder_path."A/description.txt", "r") or die("Unable to open 6.desc");
			$problem_06 = new Problem($assignment_03, "A - The Key to Cryptography", $desc_file_06, 0.0, $prob_grdmethod01, 1000, false);
			$problems[] = $problem_06;
			$prob_folds[$problem_06->name] = "A";
			
			$desc_file_07 = fopen($folder_path."B/description.txt", "r") or die("Unable to open 7.desc");
			$problem_07 = new Problem($assignment_03, "B - Red Rover", $desc_file_07, 0.0, $prob_grdmethod01, 1000, false);
			$problems[] = $problem_07;
			$prob_folds[$problem_07->name] = "B";
			
			$desc_file_08 = fopen($folder_path."C/description.txt", "r") or die("Unable to open 8.desc");
			$problem_08 = new Problem($assignment_03, "C - Lost In Translation", $desc_file_08, 0.0, $prob_grdmethod01, 1000, false);
			$problems[] = $problem_08;
			$prob_folds[$problem_08->name] = "C";
			
			$manager->persist($problem_01);		
			$manager->persist($problem_02);
			$manager->persist($problem_03);		
			$manager->persist($problem_04);
			$manager->persist($problem_05);
			$manager->persist($problem_06);
			$manager->persist($problem_07);
			$manager->persist($problem_08);
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
							
				$testcase_01 = new Testcase($prob, 1, $in_file_01, $out_file_01, $feedback_general, 0.0);
				$testcase_02 = new Testcase($prob, 2, $in_file_02, $out_file_02, $feedback_general, 0.0);
				$testcase_03 = new Testcase($prob, 3, $in_file_03, $out_file_03, $feedback_negatives, 0.0);
				$testcase_04 = new Testcase($prob, 4, $in_file_04, $out_file_04, $feedback_negatives, 0.0);
			
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
