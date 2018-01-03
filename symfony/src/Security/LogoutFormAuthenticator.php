<?php

namespace App\Security;

use App\Form\UserConfirmationType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;

class LogoutFormAuthenticator extends AbstractFormLoginAuthenticator
{
    private $csrfTokenManager;
    private $formFactory;
    private $router;
    private $session;

    public function __construct(
        CsrfTokenManagerInterface $csrfTokenManager,
        FormFactoryInterface $formFactory,
        RouterInterface $router,
        SessionInterface $session)
    {
        $this->csrfTokenManager = $csrfTokenManager;
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->session = $session;
        $this->session->start();
    }

    /**
     * @todo Fix hard-coded _csrf_token.
     */
    public function getCredentials(Request $request)
    {
        $form = $this->formFactory->create(UserConfirmationType::class);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            throw new InvalidCsrfTokenException('Invalid CSRF token.');
        }
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
