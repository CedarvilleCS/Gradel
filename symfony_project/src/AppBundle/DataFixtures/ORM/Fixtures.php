<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Role;
use AppBundle\Entity\User;
use AppBundle\Entity\Team;
use AppBundle\Entity\Course;
use AppBundle\Entity\Section;
use AppBundle\Entity\Assignment;
use AppBundle\Entity\Problem;
use AppBundle\Entity\UserSectionRole;
use AppBundle\Entity\Testcase;
use AppBundle\Entity\Submission;
use AppBundle\Entity\Language;
use AppBundle\Entity\Gradingmethod;
use AppBundle\Entity\Filetype;
use AppBundle\Entity\Feedback;
use AppBundle\Entity\TestcaseResult;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class Fixtures extends Fixture {
	
    public function load(ObjectManager $manager) {
			
		$date_format = "H:i:s m/d/Y";
		
		# ROLE Testing
		# make a role for admin, professor, student, teacher's assistant
		$student_role = new Role("Student");
		$manager->persist($student_role);

	    $prof_role = new Role("Professer");
		$manager->persist($prof_role);

	    $admin_role = new Role("Administrator");
		$manager->persist($admin_role);

	    $ta_role = new Role("Teacher's Assistant");
		$manager->persist($ta_role);
		
		# USER Testing
		# make a student, a teacher, a superuser, and a TA
		$student_user = new User("Timothy", "Smith", "timothyglensmith@cedarville.edu", new \DateTime("now"), $student_role);
		$manager->persist($student_user);
	
		$prof_user1 = new User("Keith", "Shomper", "kshomper@cedarville.edu", new \DateTime("now"), $prof_role);
		$manager->persist($prof_user1);
		
		$prof_user2 = new User("Patrick", "Dudenhofer", "patrickdude@cedarville.edu", new \DateTime("now"), $prof_role);
		$manager->persist($prof_user2);
		
		$admin_user = new User("David", "Gallagher", "gallaghd@cedarville.edu", new \DateTime("now"), $admin_role);
		$manager->persist($admin_user);

		$ta_user = new User("Blake", "Lasky", "blasky@cedarville.edu", new \DateTime("now"), $student_role);
		$manager->persist($ta_user);
		
		# COURSE Testing
		# make two regular courses and a contest
		$course_one = new Course("CS-1210", "C++ Programming", "A class where you learn how to program", false);
		$manager->persist($course_one);

		$course_two = new Course("CS-1220", "Object-Oriented Design", "A class where you learn about object-oriented design using C++", false);
		$manager->persist($course_two);

		$contest = new Course("N/A", "Cedarville University Programming Contest", "The annual programming contest open to all majors and walks of life", true);
		$manager->persist($contest);
		
		# SECTION Testing
		# make a section for each course
		$section_one = new Section($course_one, "CS-1210 (Professor Shomper)", "Fall", 2017, \DateTime::createFromFormat($date_format, "00:00:00 08/21/2017"), \DateTime::createFromFormat($date_format, "23:59:59 12/16/2017"), $prof_user1);
		$section_two = new Section($course_one, "CS-1210 (Professor Dudenhofer)", "Fall", 2017, \DateTime::createFromFormat($date_format, "00:00:00 08/21/2017"), \DateTime::createFromFormat($date_format, "23:59:59 12/16/2017"), $prof_user2);
		
		$section_three = new Section($course_two, "CS-1220 (Professor Gallagher)", "Fall", 2017, \DateTime::createFromFormat($date_format, "00:00:00 08/21/2017"), \DateTime::createFromFormat($date_format, "23:59:59 12/16/2017"), $admin_user);
		$section_four = new Section($course_two, "CS-1220 (Professor Dudenhofer)", "Spring", 2017, \DateTime::createFromFormat($date_format, "00:00:00 01/11/2017"), \DateTime::createFromFormat($date_format, "23:59:59 05/06/2017"), $prof_user2);
		
		$section_contest = new Section($contest, "2017 Cedarville University Programming Contest", "N/A", 2017, \DateTime::createFromFormat($date_format, "09:00:00 02/07/2017"), \DateTime::createFromFormat($date_format, "15:00:00 02/07/2017"), $admin_user);
		
		$manager->persist($section_one);
		$manager->persist($section_two);
		$manager->persist($section_three);
		$manager->persist($section_four);
		$manager->persist($section_contest);
		
		$manager->flush();
	}
}

?>
