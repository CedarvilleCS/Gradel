<?php

namespace AppBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
// use Symfony\Component\HttpFoundation\Request;


/*
* @Route("/account")
*/
class AccountController extends Controller
{
  /**
  * Matches /account exactly
  *
  * @Route("/account", name="account_show")
  */
  public function indexAction() {
    return $this->render('default/account/index.html.twig');
  }

}

?>
