<?php

namespace App\Security;

use App\Entity\Member;
use App\Form\U2fLoginType;
use App\FormModel\U2fLoginSubmission;
use App\Service\AuthRequestService;
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

class LoginAuthenticator extends AbstractFormLoginAuthenticator
{
    private $auth;
    private $formFactory;
    private $om;
    private $router;
    private $encoder;

    public function __construct(
        AuthRequestService $auth,
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
        $u2fSubmission = new U2fLoginSubmission();
        $u2fForm = $this
            ->formFactory
            ->create(U2fLoginType::class, $u2fSubmission);
        $u2fForm->handleRequest($request);
        if ($u2fForm->isSubmitted() && $u2fForm->isValid()) {
            return $u2fSubmission;
        }
        throw new AuthenticationException();
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $username = $credentials->username;
        $user = $this
            ->om
            ->getRepository(Member::class)->findOneBy(array(
                'username' => $username,
            ));
        return $user;
    }

    /**
     * @todo Check if the try catch block is of any use.
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        if (null === $credentials->u2fTokenResponse) {
            return false;
        }
        try {
            $this->auth->processResponse(
                $credentials->requestId,
                $credentials->username,
                $credentials->u2fTokenResponse);
        } catch (\Exception $e) {
            throw $e;
        }
        
        $password = $credentials->getPassword();
        $isPasswordValid = $this
            ->encoder
            ->isPasswordValid($user, $credentials->getPassword())
        ;
        return $isPasswordValid;
    }

    protected function getLoginUrl()
    {
        return $this->router->generate('up_authenticate');
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
            ->get('_route');
        $isRouteCorrect = $route === 'uk_authenticate';
        $isMethodCorrect = $request->isMethod('POST');
        return $isRouteCorrect && $isMethodCorrect;
    }
}