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
			
		$folder_path = "src/AppBundle/DataFixtures/ORM/";
		$date_format = "H:i:s m/d/Y";
		
		# ROLE Testing
		# make a role for admin, professor, student, teacher's assistant
		{
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
		}
		
		# USER Testing
		# make a student, a teacher, a superuser, and a TA
		{
		$prof_gallagher = new User("gallaghd", "gallaghd@cedarville.edu", '106935590466449084204', 'ya29.GlvmBCRca746USm7DxATnlo-cSXbaLgpxemqYaL4hLJ7seuuAGj9khJgvlN79oLrVk1DnBQ4-uiSR9GicIWHcePZL0-MhJBf1gDokKfMxvpa3Ov-3DDiB-uvdglR', "David", "Gallagher");
		$manager->persist($prof_gallagher);

		$prof_brauns = new User("brauns", "cbrauns@cedarville.edu", '102745040748959369206', 'ya29.GlznBM2Fspl2E8UMn7ygX1Xzy4rUcNb2Xrw_Bhoepy72GrOzsnAUCdf8ghr3VZHLkfcgLGGvmn5An3a_MSP-dl1p0G3DKytmEBXburNuu2M1qlJgtkKvdiaTAjACjQ', "Christopher", "Brauns");
		$manager->persist($prof_brauns);
		
		
		$wolf_user = new User("ewolf", "ewolf@cedarville.edu", '101057281057560942387', 'ya29.GlznBP9IhwJVmug7i4O6ymEzFBtsFIrBwDIhHZA1smkjL3jJV_o6BMt5xuC6M1Y7i57x4ND9nd54bbA8GIwvNxzEMhuYExVbI92uKd_xjQFUBSg5REqmxxgRvwHOFQ', 'Emily', 'Wolf');
		$manager->persist($wolf_user);
		
		$budd_user = new User("ebudd", "ebudd@cedarville.edu", '107801039809418360044', 'ya29.GlvnBBGCLNq4K-1qbGIHdc9UcczjkxdzHytYjf09O2U_WMi7EmybHorO3wQcMZqFLl94qfm3A1w3v9x3WXHiK5Bk-gsjh5vIZWigceXBOLSwkN-JQ8wOo2AyX9vN', "Emmett", "Budd");
		$manager->persist($budd_user);
		
		$smith_user = new User("tgsmith", "timothyglensmith@cedarville.edu", '103366426489767506763', 'ya29.GlznBB6igfKjqyrBrZneXfMGLUgK-aoHQYNyKLlpAxZT_DE7q45zW2M1op18RpKjO1zbIgRzcJFP-bxm-nL_ohm9g-b3gno2piDfnB-CWWFGLNNtuM0aWu6o1EH0JQ', 'Timothy', 'Smith');
		$manager->persist($smith_user);
		}
		
		# COURSE Testing
		# make two regular courses and a contest
		{
		$course = new Course("CS-1210", "C++ Programming", "A class where you learn how to program", false, false, false);
		$manager->persist($course);

		$contest = new Course("", "Cedarville University Programming Contest", "The annual programming contest open to all majors and walks of life", true, true, false);
		$manager->persist($contest);
		}
		
		# SECTION Testing
		# make a section for each course
		{
		$CS1210_01 = new Section($course, "CS-1210-01", "Fall", 2017, \DateTime::createFromFormat($date_format, "00:00:00 08/21/2017"), \DateTime::createFromFormat($date_format, "23:59:59 12/16/2017"), $prof_gallagher, false, false);
		$CS1210_02 = new Section($course, "CS-1210-02", "Fall", 2017, \DateTime::createFromFormat($date_format, "00:00:00 08/21/2017"), \DateTime::createFromFormat($date_format, "23:59:59 12/16/2017"), $smith_user, false, false);

		$manager->persist($CS1210_01);
		$manager->persist($CS1210_02);
		}
		
		# USERSECTIONROLE Testing
		# make all of the user/section mappings
		{
		$manager->persist(new UserSectionRole($wolf_user, $CS1210_01, $takes_role));
		$manager->persist(new UserSectionRole($budd_user, $CS1210_01, $takes_role));
		$manager->persist(new UserSectionRole($smith_user, $CS1210_01, $takes_role));
		$manager->persist(new UserSectionRole($prof_gallagher, $CS1210_01, $teach_role));
		$manager->persist(new UserSectionRole($prof_brauns, $CS1210_01, $teach_role));
		
		$manager->persist(new UserSectionRole($prof_gallagher, $CS1210_02, $takes_role));
		$manager->persist(new UserSectionRole($prof_brauns, $CS1210_02, $takes_role));
		$manager->persist(new UserSectionRole($smith_user, $CS1210_02, $teach_role));
		}
		
		# GRADINGMETHOD Testing
		{
		$method_nolatesubs = new Gradingmethod("No Late Submissions", "The end time of the project is the final time a submission will be accepted. No late work is accepted after this.");
		$method_cutoff10penalty = new Gradingmethod("Cutoff - 10% Penalty", "The cutoff time is the final time a submission will be accepted. There is a 10% penalty for submitting before this.");
		$method_cutoff00penalty = new Gradingmethod("Cutoff - No Penalty", "The cutoff time is the final time a submission will be accepted. There is no penalty for submitting before this.");
		
		$method_nopenalty = new Gradingmethod("No Attempt Penalty", "You are allowed to submit any number of attempts, and each attempt has no penalty");
		$method_10penalty = new Gradingmethod("10% Attempt Penalty", "You are allowed to submit any number of attempts, but each attempt lowers score by 10%");
		
		$manager->persist($method_nolatesubs);
		$manager->persist($method_cutoff10penalty);
		$manager->persist($method_cutoff00penalty);
		
		$manager->persist($method_nopenalty);
		$manager->persist($method_10penalty);				
		}
		
		# ASSIGNMENT Testing
		# construct9($sect, $nm, $desc, $start, $end, $cutoff, $wght, $grade, $extra)
		{
		$assignment_01 = new Assignment($CS1210_01, "Homework #1", "This is the first homework assignment", \DateTime::createFromFormat($date_format, "00:00:00 08/21/2017"), \DateTime::createFromFormat($date_format, "23:59:59 08/28/2017"), \DateTime::createFromFormat($date_format, "23:59:59 08/28/2017"), 0.0, $method_nolatesubs, false);
		$manager->persist($assignment_01);
		
		$assignment_02 = new Assignment($CS1210_02, "Homework #1", "This is the first homework assignment", \DateTime::createFromFormat($date_format, "00:00:00 08/21/2017"), \DateTime::createFromFormat($date_format, "23:59:59 08/28/2017"), \DateTime::createFromFormat($date_format, "23:59:59 08/28/2017"), 0.0, $method_nolatesubs, false);
		$manager->persist($assignment_02);
		}
		
		# TEAM Testing
		# make some teams for one of the classes
		{
		$team_01 = new Team("Wolf_01", $assignment_01);
		$team_02 = new Team("Budd_01", $assignment_01);	
		$team_03 = new Team("Smith_01", $assignment_01);
				
		$team_01->users[] = $wolf_user;
		$team_02->users[] = $budd_user;
		$team_03->users[] = $smith_user;
		
		$team_04 = new Team("Brauns_Gallagher_01", $assignment_02);
		$team_04->users[] = $prof_brauns;
		$team_04->users[] = $prof_gallagher;
		
		$manager->persist($team_01);
		$manager->persist($team_02);
		$manager->persist($team_03);
		$manager->persist($team_04);
		}
		
		# LANGUAGE Testing
		{
		$language_00 = new Language("No Language Restriction");	
		$language_01 = new Language("C");	
		$language_02 = new Language("C++");
		$language_03 = new Language("Java");
		
		$manager->persist($language_00);
		$manager->persist($language_01);
		$manager->persist($language_02);
		$manager->persist($language_03);			
		}
		
		# PROBLEM Testing
		# public function __construct12($assign, $nm, $desc, $inst, $lang, $default, $comp, $wght, $grdmeth, $attempts, $limit, $credit){
		{
		$desc_file_01 = fopen($folder_path."1.desc", "r") or die("Unable to open 1.desc");
		$desc_file_02 = fopen($folder_path."2.desc", "r") or die("Unable to open 2.desc");
		
		$problem_01 = new Problem($assignment_01, "Calculate the Sum", $desc_file_01, NULL, $language_00, NULL, "", 0.0, $method_nopenalty, 0, 1000, false);
		$problem_02 = new Problem($assignment_01, "Calculate the Difference", $desc_file_02, NULL, $language_00, NULL, "", 0.0, $method_10penalty, 10, 1000, false);
		
		$problem_03 = new Problem($assignment_02, "Calculate the Sum", $desc_file_01, NULL, $language_00, NULL, "", 0.0, $method_nopenalty, 0, 1000, false);
		$problem_04 = new Problem($assignment_02, "Calculate the Difference", $desc_file_02, NULL, $language_00, NULL, "", 0.0, $method_10penalty, 10, 1000, false);
		
		$manager->persist($problem_01);		
		$manager->persist($problem_02);
		
		$manager->persist($problem_03);		
		$manager->persist($problem_04);
		}
		
		# FEEDBACK Testing
		# public function __construct2($short, $long){
		{
		$short_file_01 = fopen($folder_path."1.short", "r") or die("Unable to open 1.short!");
		$short_file_02 = fopen($folder_path."2.short", "r") or die("Unable to open 2.short!");
		
		$long_file_01 = fopen($folder_path."1.long", "r") or die("Unable to open 1.long!");
		$long_file_02 = fopen($folder_path."2.long", "r") or die("Unable to open 2.long!");
		
		$feedback_general = new Feedback($short_file_01, $long_file_01);
		$feedback_negatives = new Feedback($short_file_02, $long_file_02);
		
		$manager->persist($feedback_general);		
		$manager->persist($feedback_negatives);		
		}
		
		# TESTCASE Testing
		# public function __construct6($prob, $seq, $in, $out, $feed, $wght){
		{
		$in_file_01 = fopen($folder_path."1.in", "r") or die("Unable to open 1.in!");
		$in_file_02 = fopen($folder_path."2.in", "r") or die("Unable to open 2.in!");
		$in_file_03 = fopen($folder_path."3.in", "r") or die("Unable to open 3.in!");
		$in_file_04 = fopen($folder_path."4.in", "r") or die("Unable to open 4.in!");
		
		$out_file_01 = fopen($folder_path."1.out", "r") or die("Unable to open 1.out!");
		$out_file_02 = fopen($folder_path."2.out", "r") or die("Unable to open 2.out!");
		$out_file_03 = fopen($folder_path."3.out", "r") or die("Unable to open 3.out!");
		$out_file_04 = fopen($folder_path."4.out", "r") or die("Unable to open 4.out!");	

		$in_file_05 = fopen($folder_path."5.in", "r") or die("Unable to open 5.in!");
		$in_file_06 = fopen($folder_path."6.in", "r") or die("Unable to open 6.in!");
		$in_file_07 = fopen($folder_path."7.in", "r") or die("Unable to open 7.in!");
		$in_file_08 = fopen($folder_path."8.in", "r") or die("Unable to open 8.in!");
		
		$out_file_05 = fopen($folder_path."5.out", "r") or die("Unable to open 5.out!");
		$out_file_06 = fopen($folder_path."6.out", "r") or die("Unable to open 6.out!");
		$out_file_07 = fopen($folder_path."7.out", "r") or die("Unable to open 7.out!");
		$out_file_08 = fopen($folder_path."8.out", "r") or die("Unable to open 8.out!");
		
		
		
		
		
		
		
		$testcase_01 = new Testcase($problem_01, 1, $in_file_01, $out_file_01, $feedback_general, 0.0);
		$testcase_02 = new Testcase($problem_01, 2, $in_file_02, $out_file_02, $feedback_general, 0.0);
		$testcase_03 = new Testcase($problem_01, 3, $in_file_03, $out_file_03, $feedback_negatives, 0.0);
		$testcase_04 = new Testcase($problem_01, 4, $in_file_04, $out_file_04, $feedback_negatives, 0.0);	
		
		$testcase_05 = new Testcase($problem_02, 1, $in_file_05, $out_file_05, $feedback_general, 0.0);
		$testcase_06 = new Testcase($problem_02, 2, $in_file_06, $out_file_06, $feedback_general, 0.0);
		$testcase_07 = new Testcase($problem_02, 3, $in_file_07, $out_file_07, $feedback_negatives, 0.0);
		$testcase_08 = new Testcase($problem_02, 4, $in_file_08, $out_file_08, $feedback_negatives, 0.0);		
		
		$manager->persist($testcase_01);
		$manager->persist($testcase_02);	
		$manager->persist($testcase_03);	
		$manager->persist($testcase_04);	
		
		$manager->persist($testcase_05);
		$manager->persist($testcase_06);	
		$manager->persist($testcase_07);	
		$manager->persist($testcase_08);
		
		
		
		
		
		
		$testcase_11 = new Testcase($problem_03, 1, $in_file_01, $out_file_01, $feedback_general, 0.0);
		$testcase_12 = new Testcase($problem_03, 2, $in_file_02, $out_file_02, $feedback_general, 0.0);
		$testcase_13 = new Testcase($problem_03, 3, $in_file_03, $out_file_03, $feedback_negatives, 0.0);
		$testcase_14 = new Testcase($problem_03, 4, $in_file_04, $out_file_04, $feedback_negatives, 0.0);	
		
		$testcase_15 = new Testcase($problem_04, 1, $in_file_05, $out_file_05, $feedback_general, 0.0);
		$testcase_16 = new Testcase($problem_04, 2, $in_file_06, $out_file_06, $feedback_general, 0.0);
		$testcase_17 = new Testcase($problem_04, 3, $in_file_07, $out_file_07, $feedback_negatives, 0.0);
		$testcase_18 = new Testcase($problem_04, 4, $in_file_08, $out_file_08, $feedback_negatives, 0.0);
		
		$manager->persist($testcase_11);
		$manager->persist($testcase_12);	
		$manager->persist($testcase_13);	
		$manager->persist($testcase_14);	
		
		$manager->persist($testcase_15);
		$manager->persist($testcase_16);	
		$manager->persist($testcase_17);	
		$manager->persist($testcase_18);		
		}
		
		# FILETYPE Testing
		# public function __construct1($ext)
		{
		$manager->persist(new Filetype("c"));
		$manager->persist(new Filetype("cpp"));
		$manager->persist(new Filetype("java"));
		$manager->persist(new Filetype("zip"));
		$manager->persist(new Filetype("tar"));
		$manager->persist(new Filetype("py"));		
		}
				
		$manager->flush();
	}
}

?>
