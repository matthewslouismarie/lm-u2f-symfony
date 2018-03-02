<?php

namespace App\Security;

use App\DataStructure\TransitingDataManager;
use App\Entity\Member;
use App\Model\BooleanObject;
use App\Model\StringObject;
use App\Service\SecureSession;
use Doctrine\Common\Persistence\ObjectManager;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use UnexpectedValueException;

class MemberAuthenticator extends AbstractFormLoginAuthenticator
{
    private $om;

    private $router;

    private $encoder;

    private $secureSession;

    public function __construct(
        SecureSession $secureSession,
        ObjectManager $om,
        RouterInterface $router)
    {
        $this->om = $om;
        $this->router = $router;
        $this->secureSession = $secureSession;
    }

    /**
     * @todo Type-check for $username and $successfulAuthentication?
     */
    public function getCredentials(Request $request)
    {
        $tdm = $this
            ->secureSession
            ->getObject(
                $request->get('sid'),
                TransitingDataManager::class
            )
        ;

        try {
            $username = $tdm
                ->getBy('key', 'username')
                ->getOnlyValue()
                ->getValue(StringObject::class)
                ->toString()
            ;
    
            $successfulAuthentication = $tdm
                ->getBy('key', 'successful_authentication')
                ->getOnlyValue()
                ->getValue(BooleanObject::class)
                ->toBoolean()
            ;
        } catch (UnexpectedValueException|InvalidArgumentException $e) {
            throw new AuthenticationException();
        }

        return [
            'username' => $username,
            'successful_authentication' => $successfulAuthentication,
        ];
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $user = $this
            ->om
            ->getRepository(Member::class)->findOneBy(array(
                'username' => $credentials['username'],
            ));

        return $user;
    }

    /**
     * @todo
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        return $credentials['successful_authentication'];
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
