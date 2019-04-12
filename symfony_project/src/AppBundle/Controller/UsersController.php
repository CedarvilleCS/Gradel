<?php

namespace AppBundle\Controller;

use AppBundle\Constants;

use AppBundle\Entity\User;
use AppBundle\Entity\Course;
use AppBundle\Entity\UserSectionRole;
use AppBundle\Entity\Section;
use AppBundle\Entity\Assignment;
use AppBundle\Entity\Team;
use AppBundle\Entity\Trial;

use AppBundle\Service\RoleService;
use AppBundle\Service\UserService;

use AppBundle\Utils\Uploader;

use Doctrine\Common\Collections\ArrayCollection;

use \DateTime;
use \DateInterval;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

use Psr\Log\LoggerInterface;

class UsersController extends Controller {
    private $logger;
    private $roleService;
    private $userService;

    public function __construct(LoggerInterface $logger,
                                RoleService $roleService,
                                UserService $userService) {
        $this->logger = $logger;
        $this->roleService = $roleService;
        $this->userService = $userService;
    }

    public function usersAction() {
        $currentUser = $this->userService->getCurrentUser();
        if (!get_class($currentUser)) {
            return $this->returnForbiddenResponse("YOU ARE NOT LOGGED IN");
        }
        if (!($currentUser->hasRole(Constants::SUPER_ROLE) || $currentUser->hasRole(Constants::ADMIN_ROLE))) {
            return $this->returnForbiddenResponse("YOU DO NOT HAVE PERMISSION TO DO THIS");
        }
        $users = $this->userService->getAllUsers();
        $roles = $this->roleService->getAllRoles();
        
        $userRoles = [];
        foreach ($users as $user) {
            $userRoles[$user->id] = [
                "username" => $user->getUsername(),
                "first" => $user->getFirstName(),
                "last" => $user->getLastName(),
                "roles" => $user->getRoles()
            ];
        }

        return $this->render("users/index.html.twig", [
            "users" => $users,
            "userRoles" => json_encode($userRoles),
            "roles" => $roles
        ]);
    }

    public function modifyPostAction(Request $request) {
        $currentUser = $this->userService->getCurrentUser();
        if (!get_class($currentUser)) {
            return $this->returnForbiddenResponse("YOU ARE NOT LOGGED IN");
        }
        if (!($currentUser->hasRole(Constants::SUPER_ROLE) || $currentUser->hasRole(Constants::ADMIN_ROLE))) {
            return $this->returnForbiddenResponse("YOU DO NOT HAVE PERMISSION TO DO THIS");
        }

        $users = $request->request->all();

        foreach ($users as $userId => $user) {
            $userToEdit = $this->userService->getUserById($userId);
            
            if ($userToEdit->getFirstName() != $user["first"]) {
                $userToEdit->setFirstName($user["first"]);
            }
            if ($userToEdit->getLastName() != $user["last"]) {
                $userToEdit->setLastName($user["last"]);
            }
            if ($userToEdit->getUsername() != $user["username"]) {
                $userToEdit->setUsername($user["username"]);
            }
            
            foreach ($userToEdit->getRoles() as $role) {
                $userToEdit->removeRole($role);
            }
            
            foreach ($user["roles"] as $role) {
                $userToEdit->addRole($role);
            }

            $this->userService->insertUser($userToEdit);
        }

        return new JsonResponse([]);
    }

    private function logError($message) {
        $errorMessage = "UsersController: ".$message;
        $this->logger->error($errorMessage);
        return $errorMessage;
    }
    
    private function returnForbiddenResponse($message) {		
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
