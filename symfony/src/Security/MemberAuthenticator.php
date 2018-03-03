<?php

namespace App\Security;

use App\DataStructure\TransitingDataManager;
use App\Entity\Member;
use App\Model\ArrayObject;
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

/**
 * @todo (Security) Prevent rerouting lower request to this.
 */
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

    public function getCredentials(Request $request)
    {
        return $this
            ->secureSession
            ->getAndRemoveObject(
                $request->get('sid'),
                TransitingDataManager::class
            )
        ;
    }

    public function getUser($tdm, UserProviderInterface $userProvider)
    {
        $username = $tdm
            ->getBy('key', 'username')
            ->getOnlyValue()
            ->getValue(StringObject::class)
            ->toString()
        ;
        $user = $this
            ->om
            ->getRepository(Member::class)->findOneBy(array(
                'username' => $username,
            ))
        ;

        return $user;
    }

    public function checkCredentials($tdm, UserInterface $user)
    {
        try {
            $checkers = $tdm
                ->getBy('key', 'checkers')
                ->getOnlyValue()
                ->getValue(ArrayObject::class)
                ->toArray()
            ;
        }
        catch (UnexpectedValueException $e) {
            throw new AuthenticationException();
        }
        foreach ($checkers as $checker)
        {
            try {
                $valids = $tdm
                    ->getBy('route', $checker)
                    ->getBy('key', 'successful_authentication')
                    ->toArray()
                ;
            } catch (UnexpectedValueException $e) {
                throw new AuthenticationException();
            }
            foreach ($valids as $valid) {
                if (true !== $valid->toBoolean()) {
                    return false;
                }
            }
        }
        return true;
    }

    protected function getLoginUrl()
    {
        return $this->router->generate('authenticate');
    }

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
