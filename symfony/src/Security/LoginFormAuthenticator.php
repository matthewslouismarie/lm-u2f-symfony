<?php

namespace App\Security;

use Symfony\Component\Security\Core\Exception\AuthenticationException;
use App\Entity\Member;
use App\Form\LoginForm;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator
{
    private $formFactory;
    private $encoder;
    private $om;
    private $router;
    private $session;

    public function __construct(
        FormFactoryInterface $formFactory,
        ObjectManager $om,
        RouterInterface $router,
        SessionInterface $session,
        UserPasswordEncoderInterface $encoder)
    {
        $this->formFactory = $formFactory;
        $this->encoder = $encoder;
        $this->om = $om;
        $this->router = $router;
        $this->session = $session;
        $this->session->start();
    }

    public function getCredentials(Request $request)
    {
        $form = $this->formFactory->create(LoginForm::class);
        $form->handleRequest($request);
        $data = $form->getData();

        return $data;
    }

    /**
     * @todo Use constants or service to access session variables.
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $username = $credentials['_username'];
        $user = $this
            ->om
            ->getRepository(Member::class)->findOneBy(array(
                'username' => $username,
        ));
        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        $isPasswordValid = $this->encoder->isPasswordValid($user, $credentials['_password']);
        if ($isPasswordValid) {
            $this->session->set('lm_u2f_symfony:username', $user->getUsername());
        }
        return $isPasswordValid;
    }

    protected function getLoginUrl()
    {
        return 'public/login';
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return;
    }

    public function supports(Request $request): bool
    {
        $isRouteCorrect = $request
            ->attributes
            ->get('_route') === 'security_login';
        $isMethodCorrect = $request->isMethod('POST');
        return $isRouteCorrect && $isMethodCorrect;
    }
}
