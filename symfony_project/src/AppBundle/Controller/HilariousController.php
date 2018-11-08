<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;

use Psr\Log\LoggerInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class HilariousController extends Controller {
    
    public function spinAction() {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        if (!get_class($user)) {
            die("USER DOES NOT EXIST!");		  
        }
        
        return $this->render('hilarious/chihuahua_spin.html.twig', []);
    }
}

?>