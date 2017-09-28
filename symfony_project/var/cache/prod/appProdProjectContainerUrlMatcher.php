<?php

use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RequestContext;

/**
 * appProdProjectContainerUrlMatcher.
 *
 * This class has been auto-generated
 * by the Symfony Routing Component.
 */
class appProdProjectContainerUrlMatcher extends Symfony\Bundle\FrameworkBundle\Routing\RedirectableUrlMatcher
{
    /**
     * Constructor.
     */
    public function __construct(RequestContext $context)
    {
        $this->context = $context;
    }

    public function match($pathinfo)
    {
        $allow = array();
        $pathinfo = rawurldecode($pathinfo);
        $trimmedPathinfo = rtrim($pathinfo, '/');
        $context = $this->context;
        $request = $this->request;
        $requestMethod = $canonicalMethod = $context->getMethod();
        $scheme = $context->getScheme();

        if ('HEAD' === $requestMethod) {
            $canonicalMethod = 'GET';
        }


        if (0 === strpos($pathinfo, '/a')) {
            if (0 === strpos($pathinfo, '/account')) {
                // account_show
                if ('/account' === $pathinfo) {
                    return array (  '_controller' => 'AppBundle\\Controller\\AccountController::indexAction',  '_route' => 'account_show',);
                }

                // account
                if ('/account' === $pathinfo) {
                    return array (  '_controller' => 'AppBundle\\Controller\\DefaultController::accountAction',  '_route' => 'account',);
                }

            }

            // auth0_login
            if ('/auth0/callback' === $pathinfo) {
                return array('_route' => 'auth0_login');
            }

            // auth0_logout
            if ('/auth0/logout' === $pathinfo) {
                return array('_route' => 'auth0_logout');
            }

        }

        // index
        if ('' === $trimmedPathinfo) {
            if (substr($pathinfo, -1) !== '/') {
                return $this->redirect($pathinfo.'/', 'index');
            }

            return array (  '_controller' => 'AppBundle\\Controller\\DefaultController::indexAction',  '_route' => 'index',);
        }

        // hwi_oauth_service_redirect
        if (0 === strpos($pathinfo, '/connect') && preg_match('#^/connect/(?P<service>[^/]++)$#s', $pathinfo, $matches)) {
            return $this->mergeDefaults(array_replace($matches, array('_route' => 'hwi_oauth_service_redirect')), array (  '_controller' => 'HWI\\Bundle\\OAuthBundle\\Controller\\ConnectController::redirectToServiceAction',));
        }

        // hwi_oauth_connect
        if ('/login' === $trimmedPathinfo) {
            if (substr($pathinfo, -1) !== '/') {
                return $this->redirect($pathinfo.'/', 'hwi_oauth_connect');
            }

            return array (  '_controller' => 'HWI\\Bundle\\OAuthBundle\\Controller\\ConnectController::connectAction',  '_route' => 'hwi_oauth_connect',);
        }

        throw 0 < count($allow) ? new MethodNotAllowedException(array_unique($allow)) : new ResourceNotFoundException();
    }
}
