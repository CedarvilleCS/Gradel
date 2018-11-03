<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use AppBundle\Entity\Submission;
use AppBundle\Entity\Role;
use AppBundle\Entity\User;
use AppBundle\Entity\Team;
use AppBundle\Entity\Course;
use AppBundle\Entity\Section;
use AppBundle\Entity\Assignment;
use AppBundle\Entity\Problem;
use AppBundle\Entity\UserSectionRole;
use AppBundle\Entity\Testcase;
use AppBundle\Entity\Language;
use AppBundle\Entity\Feedback;
use AppBundle\Entity\TestcaseResult;
use AppBundle\Entity\Trial;

use AppBundle\Service\ProblemService;
use AppBundle\Service\TrialService;
use AppBundle\Service\UserService;

use AppBundle\Utils\Uploader;
use AppBundle\Utils\Generator;
use AppBundle\Utils\Zipper;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Psr\Log\LoggerInterface;

use \DateTime;

class TrialController extends Controller {
	private $problemService;
	private $trialService;
	private $userService;

	public function __construct(LoggerInterface $logger,
	                            ProblemService $problemService,
	                            TrialService $trialService,
	                            UserService $userService) {
		$this->logger = $logger;
		$this->problemService = $problemService;
		$this->trialService = $trialService;
		$this->userService = $userService;
	}

	public function trialModifyAction(Request $request) {
		/* Get the current user */
		$user = $this->userService->getCurrentUser();
		if (!get_class($user)) {
			return $this->returnForbiddenResponse("USER DOES NOT EXIST");
		}
		
		/* Gets the gradel/symfony_project directory */
		$webDirectory = $this->get("kernel")->getProjectDir()."/";
			
		$generator = new Generator($this->getDoctrine()->getManager(), $webDirectory);			
		$uploader = new Uploader($webDirectory);
		
		$postData = $request->request->all();	
				
		/* Get the problem */
		$problemId = $postData["problem_id"];
		$problem = $this->problemService->getProblemById($problemId);
		if (!$problem) {
			return $this->returnForbiddenResponse("PROBLEM ".$problemId." DOES NOT EXIST");
		}

		$uploadedFile = $_FILES["file"];
		$aceData = $postData["ACE"];
		if (!isset($uploadedFile)) {
			if (!isset($aceData)) {
				return $this->returnForbiddenResponse("ACE EDITOR CONTENT WAS NOT PROVIDED");
			}
			
			$aceData = json_decode($aceData);
			
			/* Make a temporary directory */
			$tempDirectory = null;
			
			while (!is_dir($tempDirectory)) {
				$tempDirectory = tempnam(sys_get_temp_dir(),"");
				
				if (file_exists($tempDirectory)) {
					unlink($tempDirectory);
				}
				mkdir($tempDirectory);
			}
			$tempDirectory .= "/";

			$totalSize = 0;
			$arrayOfNames = [];
			
			if (count($aceData) < 1) {
				return $this->returnForbiddenResponse("ACE DATA CANNOT BE EMPTY");
			}

			foreach ($aceData as $aceDatum) {
				if (strlen($aceDatum->content) <= 0) {
					return $this->returnForbiddenResponse("YOUR FILE CANNOT BE EMPTY");
				}
				
				if (strlen($aceDatum->filename) <= 0) {
					return $this->returnForbiddenResponse("YOUR FILENAME CANNOT BE BLANK");
				}
				
				if (preg_match("/^[a-zA-Z0-9-_]+\.[a-zA-Z]+$/", $aceDatum->filename) <= 0) {
					return $this->returnForbiddenResponse("YOUR FILENAME IS INVALID");
				}
				
				$aceContent = $aceDatum->content;
				$filename = $aceDatum->filename;
				$totalSize += strlen($aceContent);
			
				if ($totalSize > 1024 * 1024) {
					return $this->returnForbiddenResponse("UPLOADED CODE MUST BE SMALLER THAN 1MB TOTAL");
				}
				if (!file_put_contents($tempDirectory.$filename, $aceContent, FILE_USE_INCLUDE_PATH)) {
					return $this->returnForbiddenResponse("UNABLE TO MOVE THE ACE EDITOR CONTENTS");
				}
			}
			
			$zipper = new Zipper();
			$target_file = $tempDirectory."zippy.zip";
			
			$response = $zipper->zipFiles($tempDirectory, $target_file);
				
			if ($response !== true) {
				return $this->returnForbiddenResponse($response."");
			}		
			
			/* Make a zip file and set file = fopen(zip location) */
			$file = fopen($target_file, "r");
			
			if (!$file) {
				return $this->returnForbiddenResponse("COULD NOT PROPERLY CREATE A FILE");
			}
		} else {
			if (filesize($uploadedFile["tmp_name"]) > 1024 * 1024) {
				return $this->returnForbiddenResponse("UPLOADED CODE MUST BE SMALLER THAN 1MB TOTAL");
			}

			$file = fopen($uploadedFile["tmp_name"], "r");
			$filename = "zippy.zip";
		}
		
		/* get the old trial or create a new one */
		$trial = $this->trialService->getTrialForAssignment($user, $problem);
		
		if (!$trial) {
			$trial = $this->trialService->createTrial($user, $problem);
		}
		
		$trial->last_edit_time = new \DateTime("now");
		$trial->show_description = $postData["show_description"] != "false";
		$trial->editor_height = (is_numeric($postData["editor_height"])) ? $postData["editor_height"] : 0;
				
		/* Get filename and information */
		$filename = null;
		$mainClass = null;
		$packageName = null;
		$language = null;		
		
		$response = $generator->generateFilename($filename, $language, $mainClass, $packageName, $problem, $postData);
		
		if ($response !== 1) {
			return $this->returnForbiddenResponse($response."");
		}
		
		$filename = pathinfo($target_file, PATHINFO_BASENAME);			

		$trial->file = $file;
		$trial->filename = $filename;
		$trial->language = $language;
		$trial->main_class = $mainClass;
		$trial->package_name = $packageName;

		$this->trialService->insertTrial($trial);
		
		/* Return the id of the trial */
		$response = new Response(json_encode([
			"trial_id" => $trial->id	
		]));
		return $this->returnOkResponse($response);
	}
	
	public function quickAction(Request $request) {	
		$response = $this->forward("AppBundle\Controller\TrialController::trialModifyAction");
				
		if ($response->getStatusCode() == Response::HTTP_OK) {
			return $this->forward("AppBundle\Controller\CompilationController::submitAction", [
				"trialId" => json_decode($response->getContent())->trial_id,
			]);
		} else {			
			return $response;	
		}		
	}
	
	private function logError($message) {
		$errorMessage = "TrialController: ".$message;
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

?>
