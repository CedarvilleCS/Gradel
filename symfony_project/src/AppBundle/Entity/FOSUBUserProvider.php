<?php

// Change the namespace according to your project.
namespace AppBundle\Entity;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider as BaseClass;
use Symfony\Component\Security\Core\User\UserInterface;

class FOSUBUserProvider extends BaseClass {

    public function connect(UserInterface $user, UserResponseInterface $response) {
        $property = $this->getProperty($response);
        
		
		
        $username = $response->getUsername();
        
        // On connect, retrieve the access token and the user id
        $service = $response->getResourceOwner()->getName();
        
        $setter = 'set' . ucfirst($service);
        $setter_id = $setter . 'Id';
        $setter_token = $setter . 'AccessToken';
        
        // Disconnect previously connected users
        if (null !== $previousUser = $this->userManager->findUserBy(array($property => $username))) {
            $previousUser->$setter_id(null);
            $previousUser->$setter_token(null);
            $this->userManager->updateUser($previousUser);
        }
        
        // Connect using the current user
        $user->$setter_id($username);
        $user->$setter_token($response->getAccessToken());
        $this->userManager->updateUser($user);
    }

    public function loadUserByOAuthUserResponse(UserResponseInterface $response) {
        $data = $response->getResponse();
        $username = $response->getUsername();
        $fname = $response->getFirstName();
		$lname = $response->getLastName();
        $email = $response->getEmail() ? $response->getEmail() : $username;
        $user = $this->userManager->findUserBy(array($this->getProperty($response) => $username));
        
        // If the user is new
        if (null === $user) {
            $service = $response->getResourceOwner()->getName();
            $setter = 'set' . ucfirst($service);
            $setter_id = $setter . 'Id';
            $setter_token = $setter . 'AccessToken';
            // create new user here
            $user = $this->userManager->createUser();
            $user->$setter_id($username);
            $user->$setter_token($response->getAccessToken());
            
			$un = split("\@", $email);
			$firstplus = split("\ ", $fname);
			if (strlen($firstplus[1]) > 1){
				$first = $firstplus[0] . " " . $firstplus[1];
			}
			else {
				$first = $firstplus[0];
			}
            //I have set all requested data with the user's username
            //modify here with relevant data
            $user->setUsername($email);
            $user->setEmail($email);
            $user->setFirstName($first);
			$user->setLastName($lname);
            $user->setPassword($username);
            $user->setEnabled(true);
            $this->userManager->updateUser($user);
            return $user;
        }
        
        // If the user exists, use the HWIOAuth
        $user = parent::loadUserByOAuthUserResponse($response);
        
        $serviceName = $response->getResourceOwner()->getName();
        
        $setter = 'set' . ucfirst($serviceName) . 'AccessToken';
        
        // Update the access token
        $user->$setter($response->getAccessToken());
        
        return $user;
    }
    

}