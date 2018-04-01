<?php

namespace App\Security;

use App\DataStructure\TransitingDataManager;
use App\Entity\Member;
use App\Model\ArrayObject;
use App\Model\BooleanObject;
use App\Model\StringObject;
use App\Repository\U2fTokenRepository;
use App\Service\AppConfigManager;
use App\Service\AuthenticationManager;
use App\Service\SecureSession;
use Doctrine\Common\Persistence\ObjectManager;
use Exception;
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
use Twig_Environment;

/**
 * @todo (Security) Prevent rerouting lower request to this.
 */
class MemberAuthenticator extends AbstractFormLoginAuthenticator
{
    const JUST_LOGGED_IN = "JUST_LOGGED_IN";

    private $config;

    private $om;

    private $requestManager;

    private $router;

    private $encoder;

    private $secureSession;

    private $u2fTokenRepository;

    public function __construct(
        AppConfigManager $config,
        SecureSession $secureSession,
        ObjectManager $om,
        AuthenticationManager $requestManager,
        RouterInterface $router,
        Twig_Environment $twig,
        U2fTokenRepository $u2fTokenRepository)
    {
        $this->config = $config;
        $this->om = $om;
        $this->requestManager = $requestManager;
        $this->router = $router;
        $this->secureSession = $secureSession;
        $this->u2fTokenRepository = $u2fTokenRepository;
    }

    public function getCredentials(Request $request)
    {
        $sid = $request->get('sid');

        return [
            $this
                ->secureSession
                ->getObject(
                    $sid,
                    TransitingDataManager::class
                ),
            $sid,
        ];
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $username = $credentials[0]
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

    public function checkCredentials($credentials, UserInterface $user)
    {
        try {
            $this
                ->requestManager
                ->achieveOperationTdm($credentials[0], 'authentication_processing', $credentials[1])
            ;
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @todo Add a message?
     */
    protected function getLoginUrl()
    {
        return $this->router->generate('choose_authenticate');
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $this
            ->secureSession
            ->setObject(
                self::JUST_LOGGED_IN,
                new BooleanObject(true),
                BooleanObject::class)
        ;
        return new RedirectResponse($this->router->generate('post_authentication'));        
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
