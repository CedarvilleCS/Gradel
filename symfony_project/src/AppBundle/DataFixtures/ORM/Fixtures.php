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

class Fixtures extends Fixture
{
    	public function load(ObjectManager $manager)
    	{
		
		# ROLE Testing
		# make a role for admin, professor, student, teacher's assistant
		$student_role = new Role();
		$student_role->setRoleName("Student");
		$manager->persist($student_role);

	    	$prof_role = new Role();
		$prof_role->setRoleName("Professor");
		$manager->persist($prof_role);

	    	$admin_role = new Role();
		$admin_role->setRoleName("Administrator");
		$manager->persist($admin_role);

	    	$ta_role = new Role();
		$ta_role->setRoleName("Teacher's Assistant");
		$manager->persist($ta_role);


		# USER Testing
		# make a student, a teacher, a superuser, and a TA
		$student_user = new User();
		$student_user->setFirstName('Timothy');
		$student_user->setLastName('Smith');
		$student_user->setEmail('timothyglensmith@cedarville.edu');
		$student_user->setAccessLevel($student_role);
		$student_user->updateLastLogin();
		$manager->persist($student_user);
	
		$prof_user = new User();
		$prof_user->setFirstName('Keith');
		$prof_user->setLastName('Shomper');
		$prof_user->setEmail('kshomper@cedarville.edu');
		$prof_user->setAccessLevel($prof_role);
		$prof_user->updateLastLogin();
		$manager->persist($prof_user);
		
		$admin_user = new User();
		$admin_user->setFirstName('David');
		$admin_user->setLastName('Gallagher');
		$admin_user->setEmail('gallaghd@cedarville.edu');
		$admin_user->setAccessLevel($admin_role);
		$admin_user->updateLastLogin();
		$manager->persist($admin_user);

		$ta_user = new User();
		$ta_user->setFirstName('Jonathan');
		$ta_user->setLastName('Easterday');
		$ta_user->setEmail('jeasterday@cedarville.edu');
		$ta_user->setAccessLevel($student_role);
		$ta_user->updateLastLogin();
		$manager->persist($ta_user);


		# COURSE Testing
		# make a three courses
		$course_one = new Course();
		$course_one->setCode('CS-1210');
		$course_one->setName('C++ Programming');
		$course_one->setDescription('A class where you learn how to program');
		$course_one->setIsContest(false);
		$manager->persist($course_one);

		$course_two = new Course();
		$course_two->setCode('CS-1220');
		$course_two->setName('Object-Oriented Design');
		$course_two->setDescription('A class where you learn about object-oriented design using C++');
		$course_two->setIsContest(false);
		$manager->persist($course_two);

		$contest = new Course();
		$contest->setCode('');
		$contest->setName('Cedarville University Programming Contest');
		$contest->setDescription('The annual programming contest open to all majors and walks of life');
		$contest->setIsContest(true);
		$manager->persist($contest);

		
		# SECTION Testing
		# make a section for each course

		$manager->flush();
	}
}

?>
