<?php

namespace App\Security;

use App\DataStructure\TransitingDataManager;
use App\Entity\Member;
use App\Form\LoginRequestType;
use App\FormModel\LoginRequest;
use App\Service\SecureSession;
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

class MemberAuthenticator extends AbstractFormLoginAuthenticator
{
    private $formFactory;

    private $om;

    private $router;

    private $encoder;

    private $secureSession;

    public function __construct(
        SecureSession $secureSession,
        FormFactoryInterface $formFactory,
        ObjectManager $om,
        RouterInterface $router,
        UserPasswordEncoderInterface $encoder)
    {
        $this->formFactory = $formFactory;
        $this->om = $om;
        $this->router = $router;
        $this->encoder = $encoder;
        $this->secureSession = $secureSession;
    }

    public function getCredentials(Request $request)
    {
        $tdm = $this
            ->secureSession
            ->getObject(
                $request->get('sid'),
                TransitingDataManager::class
            )
        ;

        return $tdm
            ->getBy('key', 'username')
            ->getOnlyValue()
            ->getValue()
            ->toString()
        ;

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

    /**
     * @todo
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    /**
     * @todo
     */
    protected function getLoginUrl()
    {
        return $this->router->generate('login_request');
    }

    /**
     * @todo Redirect to previously visited page.
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return new RedirectResponse($this->router->generate('successful_authentication'));
    }

    public function supports(Request $request): bool
    {
        $route = $request
            ->attributes
            ->get('_route')
        ;
        $isRouteCorrect = 'authentication_processing' === $route;
        $isMethodCorrect = $request->isMethod('GET');

        return $isRouteCorrect && $isMethodCorrect;
    }
}
