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
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator
{
    private $formFactory;
    private $om;
    private $router;
    private $session;

    public function __construct(
        FormFactoryInterface $formFactory,
        ObjectManager $om,
        RouterInterface $router,
        SessionInterface $session)
    {
        $this->formFactory = $formFactory;
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
        return $data ?? array();
    }

    /**
     * @todo Use constants or service to access session variables.
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        if (isset($credentials['_username'])) {
            $username = $credentials['_username'];
            $this->session->set('lm_u2f_symfony:username', $username);
        } else {
            $username = $this->session->get('lm_u2f_symfony:username');
        }
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
        return 'public/login';
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // return new RedirectResponse('/public');
        return;
    }

    public function supports(Request $request): bool
    {
        // $isRouteCorrect = $request->attributes->get('_route') === 'security_login';
        // $isMethodCorrect = $request->isMethod('POST');
        // return $isRouteCorrect && $isMethodCorrect;
        return true;
    }
}
/**
 * 1. Le guarde supporte toutes les requêtes.
 * 2. En cas de succès, il ne redirige pas.
 * 3. getCredentials retourne un array username et password de la requête post (et si la requête est bonne)
 * 4. Si la requête est bonne, getUser() retourne et enregistre le nouvel utilisateur, sinon, il retourne l'ancien.
 * 5. checkCredentials: toujours true pour l'instant
 */