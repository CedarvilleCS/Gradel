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

use Psr\Log\LoggerInterface;

use AppBundle\Service\ProblemService;
use AppBundle\Service\UserService;

use AppBundle\Utils\Uploader;
use AppBundle\Utils\Generator;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UploadController extends Controller {
    private $logger;
    private $problemService;
    private $userService;

    public function __construct(LoggerInterface $logger,
                                ProblemService $problemService,
                                UserService $userService) {
        $this->logger = $logger;
        $this->problemService = $problemService;
        $this->userService = $userService;
    }

    /*
        Saves a PHP array of the file contents using the Uploader utility in $data
        Returns 1 on success
    */
    public function getContentsAction(Request $request) {
        $fileInfos = [];
        $uploadedFiles = $_FILES["file"];
        $numberOfFiles = count($uploadedFiles["name"]);
        for ($i = 0; $i < $numberOfFiles; $i++) {
            if (!isset($uploadedFiles["error"][$i]) || is_array($uploadedFiles["error"][$i])) {
                return $this->returnForbiddenResponse("FILE MUST BE SMALLER THAN 1MB");
            }
        
            switch ($uploadedFiles["error"][$i]) {
                case UPLOAD_ERR_OK:
                    break;
                case UPLOAD_ERR_NO_FILE:
                    return $this->returnForbiddenResponse("NO FILE SENT");
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    return $this->returnForbiddenResponse("FILE MUST BE SMALLER THAN 1MB");
                default:
                    return $this->returnForbiddenResponse("UNKOWN ERRORS");
            }
        
            if ($uploadedFiles["size"][$i] > 1024 * 1024) {
                return $this->returnForbiddenResponse("FILE MUST BE SMALLER THAN 1MB");
            }
        
            $webDirectory = $this->get("kernel")->getProjectDir()."/";
            $uploader = new Uploader($webDirectory);

            $fileInfos[] = $uploader->getFileContents($uploadedFiles, $i);
        }
        
        $response = new Response(json_encode([
            "files" => $fileInfos
        ]));
        return $this->returnOkResponse($response);
    }

    /*
        Handles uploading code from the ACE editor on the assignment page
        It is called from the submitProblemUploadAction method
    */
    private function aceUpload(&$data, $problem_id, $postData) {
        $user = $this->userService->getCurrentUser();
        if (!get_class($user)) {
             return "USER DOES NOT EXIST";
        }
        
        $webDirectory = $this->get("kernel")->getProjectDir()."/";
        $generator = new Generator($this->getDoctrine()->getManager(), $webDirectory);
                
        /* Get the current problem */
        if (!isset($problem_id) || !($problem_id > 0)) {
            return "PROBLEM ID WAS NOT PROVIDED OR FORMATTED PROPERLY";
        }

        $problem = $this->problemService->getProblemById($problem_id);
        if (!$problem) {
            return "PROBLEM ".$problem_id." DOES NOT EXIST";
        }

        /* Get filename and information */
        $filename = null;
        $mainClass = null;
        $packageName = null;
        $language = null;
        
        $response = $generator->generateFilename($filename, $language, $mainClass, $packageName, $problem, $postData);
        
        if ($response != 1) {
            return $response;
        }		
        
        /* Save uploaded file to $webDirectory.compilation/uploads/user_id/problem */
        $webDirectory = $this->get("kernel")->getProjectDir()."/";
        $uploader = new Uploader($webDirectory);

        $uploads_directory = $uploader->createUploadDirectory($user, $problem);

        if (!file_put_contents($uploads_directory . $filename, $postData["ACE"], FILE_USE_INCLUDE_PATH)) {
             return "UNABLE TO MOVE THE ACE EDITOR CONTENTS";
        }

        $data = [
            "problem_id" => $problem->id,
            "submitted_filename" => $filename,
            "language_id" => $language->id,
            "main_class" => $mainClass,
            "package_name" => $packageName
        ];
        
        return 1;
    }

    /*
        Handles uploading code from the file input/selector on the assignment page
        It is called from the submitProblemUploadAction method
    */
    private function fileUpload(&$data, $problem_id, $postData, $uploadedFile) {
        $user = $this->userService->getCurrentUser();
        if (!get_class($user)) {
             return "USER DOES NOT EXIST";
        }

        $webDirectory = $this->get("kernel")->getProjectDir()."/";
        $generator = new Generator($this->getDoctrine()->getManager(), $webDirectory);

        /* Get the current problem */
        if (!isset($problem_id) || !($problem_id > 0)) {
            return "PROBLEM ID WAS NOT PROVIDED OR FORMATTED PROPERLY";
        }
        
        $problem = $this->problemService->getProblemById($problem_id);
        if (!$problem) {
            return "PROBLEM ".$problem_id." DOES NOT EXIST";
        }

        /* Save uploaded file to $webDirectory.compilation/uploads/user_id/ */
        $webDirectory = $this->get("kernel")->getProjectDir()."/";

        $uploader = new Uploader($webDirectory);
        $target_file = $uploader->uploadSubmissionFile($uploadedFile, $user, $problem);
        
        if ($target_file) {
            /* Get filename and information */
            $filename = null;
            $mainClass = null;
            $packageName = null;
            $language = null;
            
            $response = $generator->generateFilename($filename, $language, $mainClass, $packageName, $problem, $postData);
            
            if ($response != 1) {
                return $response;
            }	

            $data = [
                "problem_id" => $problem->id,
                "submitted_filename" => basename($uploadedFile->getClientOriginalName()),
                "language_id" => $language->id,
                "main_class" => $mainClass,
                "package_name" => $packageName,
            ];
            
            return 1;
        }
    }

    /*
        Controller action to handle the submission of code on the assignment page
    */
    public function submitProblemUploadAction($problem_id, Request $request) {
        $postData = $request->request->all();
        $files = $request->files;

        $aceData = $postData["ACE"];
        
        if ($files->get("file")) {
            $uploadedFile = $files->get("file");

            if ($uploadedFile->getClientSize() > 1024 * 1024) {
                return $this->returnForbiddenResponse("FILE MUST BE SMALLER THAN 1MB");
            }
            else if ($uploadedFile->getClientSize() <= 0) {
                return $this->returnForbiddenResponse("FILE GIVEN IS EMPTY");
            }		
            
            $data = null;
            $uploadResp = $this->fileUpload($data, $problem_id, $postData, $uploadedFile);
            
            if ($uploadResp != 1) {
                return $this->returnForbiddenResponse($uploadResp."");
            }
            
        } else if (isset($aceData) && trim($aceData) != "") {
            if (strlen($aceData) > 1024 * 1024) {
                return $this->returnForbiddenResponse("UPLOADED CODE IS TOO LONG");
            } else if (strlen($aceData) <= 0) {
                return $this->returnForbiddenResponse("UPLOADED CODE IS EMPTY");
            }
            
            $data = null;
            $uploadResp = $this->aceUpload($data, $problem_id, $postData);
            
            if ($uploadResp != 1) {
                return $this->returnForbiddenResponse($uploadResp."");
            }
        } else {
            return $this->returnForbiddenResponse("NOTHING PROVIDED TO UPLOAD");
        }

        $response = new Response(json_encode([
            "data" => $data,
        ]));
        return $this->returnOkResponse($response);
    }
    
    private function logError($message) {
        $errorMessage = "UploadController: ".$message;
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
