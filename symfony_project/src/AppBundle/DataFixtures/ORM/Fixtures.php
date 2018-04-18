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
			$course1 = new Course();
			$course1->code = "CS-1210";
			$course1->name = "C++ Programming";
			$course1->description = "An introductory course to computer programming with the C++ language";			
			$manager->persist($course1);

			$course2 = new Course();
			$course2->code = "CS-1220";
			$course2->name = "Object-Oriented Design with C++";
			$course2->description = "An introductory course to object-oriented computer programming with the C++ language";			
			$manager->persist($course2);

			$course3 = new Course();
			$course3->code = "CONT-123";
			$course3->name = "Local Programming Contest";
			$course3->description = "The local programming contest that happens every spring";			
			$course3->is_contest = true;
			$manager->persist($course3);
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
			
		$manager->flush();
	}
}

?>
