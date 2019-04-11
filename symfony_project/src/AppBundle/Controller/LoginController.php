<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Course;
use AppBundle\Entity\User;
use AppBundle\Entity\UserSectionRole;

use AppBundle\Service\SemesterService;
use AppBundle\Service\UserService;

use Auth0\SDK\Auth0;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Psr\Log\LoggerInterface;

class LoginController extends Controller {
    private $userService;
    private $semesterService;

    public function __construct(UserService $userService, SemesterService $semesterService) {
        $this->semesterService = $semesterService;
        $this->userService = $userService;
    }

    public function indexAction(Request $request) {
        $this->checkSemester();
        $user = $this->userService->getCurrentUser();
        if (get_class($user)) {
            return $this->redirectToRoute("homepage");
        }

        return $this->render("login/index.html.twig");
    }

    public function checkSemester(){
        $currSemester = $this->semesterService->getCurrentSemester();

        $year = date("Y");
        $today = date("Y/m/d");
        $today = strtotime($today);

        $springStartTime = strtotime('01/01/'.$year);
        $summerStartTime = strtotime('05/10/'.$year);
        $fallStartTime = strtotime('08/15/'.$year);

        if($springStartTime <= $today && $today < $summerStartTime){
            if($currSemester->term != "Spring" || $currSemester->year != $year){
                $this->semesterService->updateCurrentSemesterByTermAndYear("Spring", $year);

                $newSemester = $this->semesterService->createSemesterByTermAndYear("Summer", $year);
                $this->semesterService->insertSemester($newSemester);
            }
        }

        elseif($summerStartTime <= $today && $today < $fallStartTime){
            if($currSemester->term != "Summer" || $currSemester->year != $year){
                $this->semesterService->updateCurrentSemesterByTermAndYear("Summer", $year);

                $newSemester = $this->semesterService->createSemesterByTermAndYear("Fall", $year);
                $this->semesterService->insertSemester($newSemester);
            }
        }
        
        elseif($summerStartTime <= $today && $today < $fallStartTime){
            if($currSemester->term != "Fall" || $currSemester->year != $year){
                $this->semesterService->updateCurrentSemesterByTermAndYear("Fall", $year);

                $newSemester = $this->semesterService->createSemesterByTermAndYear("Spring", $year+1);
                $this->semesterService->insertSemester($newSemester);
            }   
        }
    }
}
?>
