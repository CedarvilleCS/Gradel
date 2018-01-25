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
use AppBundle\Entity\Feedback;
use AppBundle\Entity\TestcaseResult;
use AppBundle\Entity\Query;

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

			$course2 = new Course("CONT-123", "Local Programming Contest", "The local programming contest", true, false, false);
			$manager->persist($course2);
		}
		
		# SECTION Testing
		{
			$CS1210_01 = new Section($course1, "CS-1210-01", "Spring", 2018, \DateTime::createFromFormat($date_format, "00:00:00 11/21/2017"), \DateTime::createFromFormat($date_format, "23:59:59 05/31/2018"), false, false);
			$contest = new Section($course2, "2018 Local Programming Contest", "Spring", 2018, \DateTime::createFromFormat($date_format, "00:00:00 01/05/2018"), \DateTime::createFromFormat($date_format, "23:59:59 05/31/2018"), false, false);
			$manager->persist($CS1210_01);
			$manager->persist($contest);
		}
		
		# USERSECTIONROLE Testing
		{
			$manager->persist(new UserSectionRole($wolf_user, $CS1210_01, $takes_role));
			$manager->persist(new UserSectionRole($budd_user, $CS1210_01, $takes_role));
			$manager->persist(new UserSectionRole($prof_gallagher, $CS1210_01, $takes_role));
			$manager->persist(new UserSectionRole($brauns_user, $CS1210_01, $takes_role));
			$manager->persist(new UserSectionRole($smith_user, $CS1210_01, $teach_role));
			
			$manager->persist(new UserSectionRole($wolf_user, $contest, $takes_role));
			$manager->persist(new UserSectionRole($budd_user, $contest, $takes_role));
			$manager->persist(new UserSectionRole($prof_gallagher, $contest, $takes_role));
			$manager->persist(new UserSectionRole($brauns_user, $contest, $takes_role));
			$manager->persist(new UserSectionRole($smith_user, $contest, $judge_role));
		}
		
		# ASSIGNMENT Testing
		{		
			
			$assignment_01 = new Assignment($CS1210_01, 
									"Homework #1", "This is the first homework assignment", 
									\DateTime::createFromFormat($date_format, "00:00:00 11/30/2017"), 
									\DateTime::createFromFormat($date_format, "23:59:59 05/30/2018"), 
									\DateTime::createFromFormat($date_format, "23:59:59 05/30/2018"), 1, 0.00, false);
			$manager->persist($assignment_01);
			
			$assignment_02 = new Assignment($CS1210_01, 
									"Homework #2", "This is the second homework assignment", 
									\DateTime::createFromFormat($date_format, "00:00:00 11/30/2017"), 
									\DateTime::createFromFormat($date_format, "23:59:59 05/30/2018"), 
									\DateTime::createFromFormat($date_format, "23:59:59 05/30/2018"), 1, 0.00, false);
			$manager->persist($assignment_02);
						
			$assignment_03 = new Assignment($contest, 
									"Practice Contest",
									"This is the practice", 
									\DateTime::createFromFormat($date_format, "09:00:00 01/24/2018"), 
									\DateTime::createFromFormat($date_format, "10:30:00 01/24/2018"), 
									\DateTime::createFromFormat($date_format, "10:30:00 01/24/2018"),
									20,
									0,
									20,
									20);
			$manager->persist($assignment_03);
			
			$assignment_04 = new Assignment($contest, 
									"Actual Contest",
									"This is the actual contest", 
									\DateTime::createFromFormat($date_format, "11:00:00 01/24/2018"), 
									\DateTime::createFromFormat($date_format, "17:00:00 01/24/2018"), 
									\DateTime::createFromFormat($date_format, "17:00:00 01/24/2018"),
									20,
									0,
									20,
									20);
			$manager->persist($assignment_04);
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
			
			# ASSIGNMENT 2
			$team_02_1 = new Team("Wolf_01", $assignment_02);
			$team_02_1->users[] = $wolf_user;
			
			$team_02_2 = new Team("Budd_01", $assignment_02);	
			$team_02_2->users[] = $budd_user;
			
			$team_02_3 = new Team("Gallagher_01", $assignment_02);
			$team_02_3->users[] = $prof_gallagher;
			
			$team_02_4 = new Team("Brauns_01", $assignment_02);
			$team_02_4->users[] = $brauns_user;
			
			$manager->persist($team_02_1);
			$manager->persist($team_02_2);
			$manager->persist($team_02_3);
			$manager->persist($team_02_4);
			
			# ASSIGNMENT 2
			$team_02_1 = new Team("Wolf_01", $assignment_02);
			$team_02_1->users[] = $wolf_user;
			
			$team_02_2 = new Team("Budd_01", $assignment_02);	
			$team_02_2->users[] = $budd_user;
			
			$team_02_3 = new Team("Gallagher_01", $assignment_02);
			$team_02_3->users[] = $prof_gallagher;
			
			$team_02_4 = new Team("Brauns_01", $assignment_02);
			$team_02_4->users[] = $brauns_user;
			
			$manager->persist($team_02_1);
			$manager->persist($team_02_2);
			$manager->persist($team_02_3);
			$manager->persist($team_02_4);
			
			# ASSIGNMENT 3
			$team_03_1 = new Team("Wolf Bytes", $assignment_03);
			$team_03_1->users[] = $wolf_user;
			
			$team_03_2 = new Team("Buddiful Code", $assignment_03);	
			$team_03_2->users[] = $budd_user;
			
			$team_03_3 = new Team("Gallagorithms", $assignment_03);
			$team_03_3->users[] = $prof_gallagher;
			
			$team_03_4 = new Team("Brogrammers", $assignment_03);
			$team_03_4->users[] = $brauns_user;
			
			$manager->persist($team_03_1);
			$manager->persist($team_03_2);
			$manager->persist($team_03_3);
			$manager->persist($team_03_4);
			
			# ASSIGNMENT 4
			$team_04_1 = new Team("Wolf Bytes", $assignment_04);
			$team_04_1->users[] = $wolf_user;
			
			$team_04_2 = new Team("Buddiful Code", $assignment_04);	
			$team_04_2->users[] = $budd_user;
			
			$team_04_3 = new Team("Gallagorithms", $assignment_04);
			$team_04_3->users[] = $prof_gallagher;
			
			$team_04_4 = new Team("Brogrammers", $assignment_04);
			$team_04_4->users[] = $brauns_user;
			
			$manager->persist($team_04_1);
			$manager->persist($team_04_2);
			$manager->persist($team_04_3);
			$manager->persist($team_04_4);
		}
		
		# LANGUAGE Testing
		{
			$def_c = fopen($folder_path."default_code/default.c", "r") or die("Unable to open default.c");
			$def_cpp = fopen($folder_path."default_code/default.cpp", "r") or die("Unable to open default.cpp");
			$def_java = fopen($folder_path."default_code/default.java", "r") or die("Unable to open default.java");
			
			$def_py = fopen($folder_path."default_code/default.py", "r") or die("Unable to open default.py");
			$def_py2 = fopen($folder_path."default_code/default.py", "r") or die("Unable to open default.py");
			$def_php = fopen($folder_path."default_code/default.phpp", "r") or die("Unable to open default.php");
			
			$language_C = new Language("C", ".c", "c_cpp", $def_c);	
			$language_CPP = new Language("C++", ".cpp", "c_cpp", $def_cpp);
			$language_JAVA = new Language("Java", ".java", "java", $def_java);
			$language_PHP = new Language("PHP", ".php", "php", $def_php);
			$language_PY2 = new Language("Python 2", ".py", "python", $def_py);
			$language_PY3 = new Language("Python 3", ".py", "python", $def_py2);
			
			$manager->persist($language_C);
			$manager->persist($language_CPP);
			$manager->persist($language_JAVA);
			$manager->persist($language_PHP);
			$manager->persist($language_PY2);
			$manager->persist($language_PY3);			
		}
		
		# PROBLEM Testing
		{
			$problems = [];
			
			// put new testcases in a folder and map the problem to the name here
			// if the new problem has the same name as an old problem, change it
			$prob_folds = [];
			
			# HOMEWORK 1 For CS-1210-01
			$desc_file_01 = fopen($folder_path."sum/description.txt", "r") or die("Unable to open 1.desc");
			$problem_01 = new Problem($assignment_02, "Calculate the Sum", stream_get_contents($desc_file_01), 1, 1000, false, 0, 0, 0, false, "Long", true, "Both", true, 1, [NULL, 4]);
			$problems[] = $problem_01;
			$prob_folds[$problem_01->name] = "sum";
			
			$desc_file_02 = fopen($folder_path."diff/description.txt", "r") or die("Unable to open 2.desc");
			$problem_02 = new Problem($assignment_02, "Calculate the Difference", stream_get_contents($desc_file_02), 1, 1000, false, 0, 0, 0, false, "Long", true, "Both", true, 1, [NULL, 4]);
			$problems[] = $problem_02;
			$prob_folds[$problem_02->name] = "diff";
			
			$desc_file_03 = fopen($folder_path."prod/description.txt", "r") or die("Unable to open 3.desc");
			$problem_03 = new Problem($assignment_02, "Calculate the Product", stream_get_contents($desc_file_03), 1, 1000, false, 0, 0, 0, false, "Long", true, "Both", true, 1, [NULL, 4]);
			$problems[] = $problem_03;
			$prob_folds[$problem_03->name] = "prod";
			
			$desc_file_04 = fopen($folder_path."quot/description.txt", "r") or die("Unable to open 4.desc");
			$problem_04 = new Problem($assignment_02, "Calculate the Quotient", stream_get_contents($desc_file_04), 1, 1000, false, 0, 0, 0, false, "Long", true, "Both", true, 1, [NULL, 4]);
			$problems[] = $problem_04;
			$prob_folds[$problem_04->name] = "quot";
			
			
			# HOMEWORK 2 For CS-1210-01
			$desc_file_11 = fopen($folder_path."sum/description.txt", "r") or die("Unable to open 1.desc");
			$problem_11 = new Problem($assignment_01, "Get the Sum", stream_get_contents($desc_file_11), 2, 1000, false, 0, 0, 0, false, "Long", true, "Both", true, 1, [NULL, 4]);
			$problems[] = $problem_11;
			$prob_folds[$problem_11->name] = "sum";
			
			$desc_file_12 = fopen($folder_path."diff/description.txt", "r") or die("Unable to open 2.desc");
			$problem_12 = new Problem($assignment_01, "Get the Difference", stream_get_contents($desc_file_12), 2, 1000, false, 0, 0, 0, false, "Long", true, "Both", true, 1, [NULL, 4]);
			$problems[] = $problem_12;
			$prob_folds[$problem_12->name] = "diff";
			
			$desc_file_13 = fopen($folder_path."prod/description.txt", "r") or die("Unable to open 3.desc");
			$problem_13 = new Problem($assignment_01, "Get the Product", stream_get_contents($desc_file_13), 2, 1000, false, 0, 0, 0, false, "Long", true, "Both", true, 1, [NULL, 4]);
			$problems[] = $problem_13;
			$prob_folds[$problem_13->name] = "prod";
			
			$desc_file_14 = fopen($folder_path."quot/description.txt", "r") or die("Unable to open 4.desc");
			$problem_14 = new Problem($assignment_01, "Get the Quotient", stream_get_contents($desc_file_14), 2, 1000, false, 0, 0, 0, false, "Long", true, "Both", true, 1, [NULL, 4]);
			$problems[] = $problem_14;
			$prob_folds[$problem_14->name] = "quot";
			
			
			# PRACTICE CONTEST
			$desc_file_05 = fopen($folder_path."Z/description.txt", "r") or die("Unable to open 5.desc");
			$problem_05 = new Problem($assignment_03, "Z - Happy Trails", stream_get_contents($desc_file_05), 1, 1000, false, 0, 0, 0, true, "None", false, "None", true, 1, [NULL, 4]);
			$problems[] = $problem_05;
			$prob_folds[$problem_05->name] = "Z";
			
			# ACTUAL CONTEST
			$desc_file_06 = fopen($folder_path."A/description.txt", "r") or die("Unable to open 6.desc");
			$problem_06 = new Problem($assignment_04, "A - The Key to Cryptography", stream_get_contents($desc_file_06), 1, 1000, false, 0, 0, 0, true, "None", false, "None", true, 1, [NULL, 4]);
			$problems[] = $problem_06;
			$prob_folds[$problem_06->name] = "A";
			
			$desc_file_07 = fopen($folder_path."B/description.txt", "r") or die("Unable to open 7.desc");
			$problem_07 = new Problem($assignment_04, "B - Red Rover", stream_get_contents($desc_file_07), 2, 1000, false, 0, 0, 0, true, "None", false, "None", true, 1, [NULL, 4]);
			$problems[] = $problem_07;
			$prob_folds[$problem_07->name] = "B";
			
			$desc_file_08 = fopen($folder_path."C/description.txt", "r") or die("Unable to open 8.desc");
			$problem_08 = new Problem($assignment_04, "C - Lost In Translation", stream_get_contents($desc_file_08), 3, 1000, false, 0, 0, 0, true, "None", false, "None", true, 1, [NULL, 4]);
			$problems[] = $problem_08;
			$prob_folds[$problem_08->name] = "C";
			
			
			$manager->persist($problem_01);		
			$manager->persist($problem_02);
			$manager->persist($problem_03);		
			$manager->persist($problem_04);
			$manager->persist($problem_11);		
			$manager->persist($problem_12);
			$manager->persist($problem_13);		
			$manager->persist($problem_14);
			
			
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
				$problang_04 = new ProblemLanguage($language_PHP, $prob, NULL, NULL);			
				$problang_05 = new ProblemLanguage($language_PY2, $prob, NULL, NULL);
				$problang_06 = new ProblemLanguage($language_PY3, $prob, NULL, NULL);
				
				$manager->persist($problang_01);
				$manager->persist($problang_02);
				$manager->persist($problang_03);	
				$manager->persist($problang_04);
				$manager->persist($problang_05);
				$manager->persist($problang_06);
			}
			
		}
		
		# FEEDBACK Testing
		{
			$short_file_01 = fopen($folder_path."1.short", "r") or die("Unable to open 1.short!");			
			$long_file_01 = fopen($folder_path."1.long", "r") or die("Unable to open 1.long!");
			
			$short_file_02 = fopen($folder_path."2.short", "r") or die("Unable to open 2.short!");			
			$long_file_02 = fopen($folder_path."2.long", "r") or die("Unable to open 2.long!");
			
			$feedback_general = new Feedback(stream_get_contents($short_file_01), stream_get_contents($long_file_01));
			$feedback_negatives = new Feedback(stream_get_contents($short_file_02), stream_get_contents($long_file_02));
			
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
							
				$testcase_01 = new Testcase($prob, 1, stream_get_contents($in_file_01), NULL, stream_get_contents($out_file_01), $feedback_general, 1, false);
				$testcase_02 = new Testcase($prob, 2, stream_get_contents($in_file_02), NULL, stream_get_contents($out_file_02), $feedback_general, 2, false);
				$testcase_03 = new Testcase($prob, 3, stream_get_contents($in_file_03), NULL, stream_get_contents($out_file_03), $feedback_negatives, 3, false);
				$testcase_04 = new Testcase($prob, 4, stream_get_contents($in_file_04), NULL, stream_get_contents($out_file_04), $feedback_negatives, 6, false);
			
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
