<?php

namespace App\Security;

use App\Entity\Member;
use App\Form\LoginForm;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator
{
    private $formFactory;
    private $om;
    private $router;

    public function __construct(
        FormFactoryInterface $formFactory,
        ObjectManager $om,
        RouterInterface $router)
    {
        $this->formFactory = $formFactory;
        $this->om = $om;
        $this->router = $router;
    }

    public function getCredentials(Request $request)
    {
        $form = $this->formFactory->create(LoginForm::class);
        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            throw new AuthenticationException();
        }
        $data = $form->getData();
        return $data;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $username = $credentials->getUsername();
        $user = $this
            ->om
            ->getRepository(Member::class)->findOneBy(array(
                'username' => $username,
            ));
        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        $password = $credentials->getPassword();
        if ('hello' === $password) {
            return true;
        } else {
            return false;
        }
    }

    protected function getLoginUrl()
    {
        return $this->router->generate('security_login');
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return new RedirectResponse('/public');
    }

    public function supports(Request $request): bool
    {
        $isRouteCorrect = $request->attributes->get('_route') === 'security_login';
        $isMethodCorrect = $request->isMethod('POST');
        return $isRouteCorrect && $isMethodCorrect;
    }
}
