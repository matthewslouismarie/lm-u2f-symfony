<?php

namespace App\Security;

use App\Form\UserConfirmationType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;

/**
 * @todo Check this is the correct way to log out the user in Symfony.
 */
class LogoutAuthenticator extends AbstractFormLoginAuthenticator
{
    private $formFactory;

    private $router;

    public function __construct(
        FormFactoryInterface $formFactory,
        RouterInterface $router)
    {
        $this->formFactory = $formFactory;
        $this->router = $router;
    }

    public function getCredentials(Request $request)
    {
        $form = $this
            ->formFactory
            ->create(UserConfirmationType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            return array();
        }

        throw new AuthenticationException();
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        return null;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    protected function getLoginUrl()
    {
        return $this->router->generate('login_request');
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $url = $this->router->generate('homepage');

        return new RedirectResponse($url);
    }

    public function supports(Request $request): bool
    {
        $route = $request
            ->attributes
            ->get('_route');
        $isRouteCorrect = 'logout' === $route;
        $isMethodCorrect = $request->isMethod('POST');

        return $isRouteCorrect && $isMethodCorrect;
    }
}
