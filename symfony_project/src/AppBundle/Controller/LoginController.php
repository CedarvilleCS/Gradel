<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Course;
use AppBundle\Entity\User;
use AppBundle\Entity\UserSectionRole;

use AppBundle\Service\UserService;

use Auth0\SDK\Auth0;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Psr\Log\LoggerInterface;

class LoginController extends Controller {
    private $userService;

    public function __construct(UserService $userService) {
        $this->userService = $userService;
    }

    public function indexAction(Request $request) {
        $user = $this->userService->getCurrentUser();
        if (get_class($user)) {
            return $this->redirectToRoute("homepage");
        }

        return $this->render("login/index.html.twig");
    }
}

?>
