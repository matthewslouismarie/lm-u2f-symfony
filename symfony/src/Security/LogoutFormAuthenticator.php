<?php

namespace App\Security;

use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class LogoutFormAuthenticator extends AbstractFormLoginAuthenticator
{
    private $router;
    private $session;

    public function __construct(
        RouterInterface $router,
        SessionInterface $session)
    {
        $this->router = $router;
        $this->session = $session;
        $this->session->start();
    }

    public function getCredentials(Request $request)
    {
        return array();
    }

    /**
     * @todo Use constants or service to access session variables.
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $this->session->set('lm_u2f_symfony:username', null);
        return null;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    protected function getLoginUrl()
    {
        return $this->router->generate('security_login');
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new RedirectResponse(
            $this->router->generate('logout')            
        );
    }

    /**
     * @todo Redirect to previously visited page.
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return new RedirectResponse(
            $this->router->generate('homepage')
        );
    }

    public function supports(Request $request): bool
    {
        $isRouteCorrect = $request
            ->attributes
            ->get('_route') === 'logout';
        $isMethodCorrect = $request->isMethod('POST');
        return $isRouteCorrect && $isMethodCorrect;
    }
}
