<?php

namespace AppBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SubmitController extends Controller
{
 
  public function submitAction($project_id=1) {

    $uploadMessage = "";
    $target_dir = "/var/www/gradel_dev/budd/Gradel/symfony_project/compilation/temp/"; // Specify an upload location
    $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
    $uploadOk = 1;
    $imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);

	// this directory is /var/www/gradel_dev/user/gradel/symfony_project/compilation/temp
	$web_dir = $this->get('kernel')->getProjectDir();
	// save uploaded file to compilation/temp/submission_id/files
	
	// make a new submission if upload was successful
    // $submission_entity = new Submission($problem_entity, $team_entity, $user_entity);	
		
	// $em = $this->getDoctrine()->getManager(); 
	// $em->persist($submission_entity);
	// $em->flush();
	
    // Check if file already exists
    if (file_exists($target_file)) {
        echo "Overwriting existing file\n";
        $uploadOk = 1; // Change to 0 if you don't want it to upload and overwrite. You can add a hash to the name or something.
    }
    // Check file size
    if ($_FILES["fileToUpload"]["size"] > 500000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }
    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    // if everything is ok, try to upload file
    } else {
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }

	# call timothy's controller
	#return $this->redirectToRoute('submit', array('submitted_filename' => $target_file, 'submission_id' => $submission_entity->id));
    return $this->render('courses/assignments/submit/index.html.twig', [
			'project_id' => $project_id,
    ]);
  }
}

?>
