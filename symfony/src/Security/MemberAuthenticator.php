<?php

namespace App\Security;

use App\DataStructure\TransitingDataManager;
use App\Entity\Member;
use App\Model\ArrayObject;
use App\Model\BooleanObject;
use App\Model\StringObject;
use App\Repository\U2fTokenRepository;
use App\Service\AppConfigManager;
use App\Service\IdentityCheck\RequestManager;
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
        RequestManager $requestManager,
        RouterInterface $router,
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
        return $this
            ->requestManager
            ->isIdentityCheckedFromObject($tdm)
        ;
    }

    protected function getLoginUrl()
    {
        return $this->router->generate('authenticate');
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $userNU2fTokens = count($this->u2fTokenRepository->getU2fTokens($token->getUser()));
        $nU2fTokensRequired = $this->config->getIntSetting(AppConfigManager::POST_AUTH_N_U2F_KEYS);
        if ($nU2fTokensRequired <= $userNU2fTokens) {
            return new RedirectResponse($this->router->generate('successful_authentication'));
        } else {
            return new RedirectResponse($this->router->generate('register_u2f_key'));
        }
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
