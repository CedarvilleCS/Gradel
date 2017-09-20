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

	    $prof_role = new Role("Professor");
		$manager->persist($prof_role);

	    $admin_role = new Role("Administrator");
		$manager->persist($admin_role);

	    $helps_role = new Role("Helps");
		$manager->persist($helps_role);
		
		$takes_role = new Role("Takes");
		$manager->persist($takes_role);
		
		$teach_role = new Role("Teaches");
		$manager->persist($teach_role);
		
		$judge_role = new Role("Judges");
		$manager->persist($judge_role);
		
		# USER Testing
		# make a student, a teacher, a superuser, and a TA
		$prof_user1 = new User("Keith", "Shomper", "kshomper@cedarville.edu", new \DateTime("now"), $prof_role);
		$manager->persist($prof_user1);
		
		$prof_user2 = new User("Patrick", "Dudenhofer", "patrickdude@cedarville.edu", new \DateTime("now"), $prof_role);
		$manager->persist($prof_user2);
		
		$admin_user = new User("David", "Gallagher", "gallaghd@cedarville.edu", new \DateTime("now"), $admin_role);
		$manager->persist($admin_user);

		$student_user1 = new User("Emily", "Wolf", "ewolf@cedarville.edu", new \DateTime("now"), $student_role);
		$manager->persist($student_user1);
		
		$student_user2 = new User("Emmett", "Budd", "ebudd@cedarville.edu", new \DateTime("now"), $student_role);
		$manager->persist($student_user2);
		
		$student_user3 = new User("Chris", "Brauns", "cbrauns@cedarville.edu", new \DateTime("now"), $student_role);
		$manager->persist($student_user3);
		
		$student_user4 = new User("Timothy", "Smith", "timothyglensmith@cedarville.edu", new \DateTime("now"), $student_role);
		$manager->persist($student_user4);
		
		$ta_user1 = new User("Jonathan", "Easterday", "jeasterday@cedarville.edu", new \DateTime("now"), $student_role);
		$manager->persist($ta_user1);
		
		$ta_user2 = new User("Tyler", "Drake", "tylerdrake@cedarville.edu", new \DateTime("now"), $student_role);
		$manager->persist($ta_user2);
		
		# COURSE Testing
		# make two regular courses and a contest
		$course_one = new Course("CS-1210", "C++ Programming", "A class where you learn how to program", false, false, false);
		$manager->persist($course_one);

		$course_two = new Course("CS-1220", "Object-Oriented Design", "A class where you learn about object-oriented design using C++", false, false, false);
		$manager->persist($course_two);

		$contest = new Course("", "Cedarville University Programming Contest", "The annual programming contest open to all majors and walks of life", true, true, false);
		$manager->persist($contest);
		
		# SECTION Testing
		# make a section for each course
		$section_one = new Section($course_one, "CS-1210-01", "Fall", 2017, \DateTime::createFromFormat($date_format, "00:00:00 08/21/2017"), \DateTime::createFromFormat($date_format, "23:59:59 12/16/2017"), $prof_user1, false, false);
		$section_two = new Section($course_one, "CS-1210-02", "Fall", 2017, \DateTime::createFromFormat($date_format, "00:00:00 08/21/2017"), \DateTime::createFromFormat($date_format, "23:59:59 12/16/2017"), $prof_user2, false, false);
		
		$section_three = new Section($course_two, "CS-1220-01", "Fall", 2017, \DateTime::createFromFormat($date_format, "00:00:00 08/21/2017"), \DateTime::createFromFormat($date_format, "23:59:59 12/16/2017"), $admin_user, false, false);
		$section_four = new Section($course_two, "CS-1220-01", "Spring", 2017, \DateTime::createFromFormat($date_format, "00:00:00 01/11/2017"), \DateTime::createFromFormat($date_format, "23:59:59 05/06/2017"), $prof_user2, false, false);
		
		$section_contest1 = new Section($contest, "2017 Cedarville University Programming Contest", "", 2017, \DateTime::createFromFormat($date_format, "09:00:00 02/07/2017"), \DateTime::createFromFormat($date_format, "15:00:00 02/07/2017"), $admin_user, true, false);
		$section_contest2 = new Section($contest, "2016 Cedarville University Programming Contest", "", 2017, \DateTime::createFromFormat($date_format, "09:00:00 02/08/2016"), \DateTime::createFromFormat($date_format, "15:00:00 02/08/2016"), $admin_user, true, false);
		
		$manager->persist($section_one);
		$manager->persist($section_two);
		$manager->persist($section_three);
		$manager->persist($section_four);
		$manager->persist($section_contest1);
		$manager->persist($section_contest2);
		
		
		# USERSECTIONROLE Testing
		# make all of the user/section mappings
		$manager->persist(new UserSectionRole($student_user1, $section_one, $takes_role));
		$manager->persist(new UserSectionRole($student_user2, $section_one, $takes_role));
		$manager->persist(new UserSectionRole($prof_user1, $section_one, $teach_role));
		$manager->persist(new UserSectionRole($ta_user1, $section_one, $helps_role));
		
		$manager->persist(new UserSectionRole($student_user3, $section_two, $takes_role));
		$manager->persist(new UserSectionRole($student_user4, $section_two, $takes_role));
		$manager->persist(new UserSectionRole($prof_user2, $section_two, $teach_role));
		$manager->persist(new UserSectionRole($ta_user2, $section_two, $helps_role));
		
		$manager->persist(new UserSectionRole($student_user3, $section_three, $takes_role));
		$manager->persist(new UserSectionRole($student_user2, $section_three, $takes_role));
		$manager->persist(new UserSectionRole($admin_user, $section_three, $teach_role));
		
		$manager->persist(new UserSectionRole($student_user1, $section_four, $takes_role));
		$manager->persist(new UserSectionRole($student_user4, $section_four, $takes_role));
		$manager->persist(new UserSectionRole($prof_user2, $section_four, $teach_role));
		
		$manager->persist(new UserSectionRole($student_user1, $section_contest1, $takes_role));
		$manager->persist(new UserSectionRole($student_user2, $section_contest1, $takes_role));
		$manager->persist(new UserSectionRole($student_user3, $section_contest1, $takes_role));
		$manager->persist(new UserSectionRole($student_user4, $section_contest1, $takes_role));
		$manager->persist(new UserSectionRole($prof_user1, $section_contest1, $judge_role));
		$manager->persist(new UserSectionRole($prof_user2, $section_contest1, $judge_role));
		$manager->persist(new UserSectionRole($admin_user, $section_contest1, $judge_role));			
		
		$manager->flush();
	}
}

?>
