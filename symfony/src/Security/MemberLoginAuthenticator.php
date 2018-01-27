<?php

namespace App\Security;

use App\Entity\Member;
use App\Form\LoginRequestType;
use App\FormModel\LoginRequest;
use App\Service\U2fAuthenticationManager;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;

class MemberLoginAuthenticator extends AbstractFormLoginAuthenticator
{
    private $auth;

    private $formFactory;

    private $om;

    private $router;

    private $encoder;

    public function __construct(
        U2fAuthenticationManager $auth,
        FormFactoryInterface $formFactory,
        ObjectManager $om,
        RouterInterface $router,
        UserPasswordEncoderInterface $encoder)
    {
        $this->auth = $auth;
        $this->formFactory = $formFactory;
        $this->om = $om;
        $this->router = $router;
        $this->encoder = $encoder;
    }

    public function getCredentials(Request $request)
    {
        $loginRequest = new LoginRequest();
        $form = $this
            ->formFactory
            ->create(LoginRequestType::class, $loginRequest);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            return $loginRequest->getUsername();
        }

        throw new AuthenticationException();
    }

    public function getUser($username, UserProviderInterface $userProvider)
    {
        $user = $this
            ->om
            ->getRepository(Member::class)->findOneBy(array(
                'username' => $username,
            ));

        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    protected function getLoginUrl()
    {
        return $this->router->generate('login_request');
    }

    /**
     * @todo Redirect to previously visited page.
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return new RedirectResponse($this->router->generate('homepage'));
    }

    public function supports(Request $request): bool
    {
        $route = $request
            ->attributes
            ->get('_route')
        ;
        $isRouteCorrect = 'finalize_login' === $route;
        $isMethodCorrect = $request->isMethod('POST');

        return $isRouteCorrect && $isMethodCorrect;
    }
}
