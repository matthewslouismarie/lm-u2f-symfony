<?php

namespace App\Security;

use App\DataStructure\TransitingDataManager;
use App\Entity\Member;
use App\Model\ArrayObject;
use App\Model\BooleanObject;
use App\Model\StringObject;
use App\Repository\U2fTokenRepository;
use App\Security\Token\AuthenticationToken;
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
class TmpAuthenticator extends AbstractFormLoginAuthenticator
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
        return $this->secureSession->getObject($request->get("sid"), AuthenticationToken::class);
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $user = $this
            ->om
            ->getRepository(Member::class)->findOneBy(array(
                'username' => $credentials->getUsername(),
            ))
        ;

        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        if (null !== $user) {
            return true;
        } else {
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
            ->remove($request->get("sid"))
        ;
        return new RedirectResponse($this->router->generate('post_authentication'));        
    }

    public function supports(Request $request): bool
    {
        $route = $request
            ->attributes
            ->get('_route')
        ;
        $isRouteCorrect = 'tmp_authentication_processing' === $route;
        $isMethodCorrect = $request->isMethod('GET');

        if ($isRouteCorrect && $isMethodCorrect) {
            return true;
        } else {
            return false;
        }
    }
}
