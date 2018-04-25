<?php

declare(strict_types=1);

namespace App\Service\Authentifier;

use App\Enum\Setting;
use App\Repository\U2fTokenRepository;
use App\Service\AppConfigManager;
use App\Service\SecureSession;
use LM\AuthAbstractor\Controller\AuthenticationKernel;
use LM\AuthAbstractor\Challenge\CredentialChallenge;
use LM\AuthAbstractor\Challenge\ExistingUsernameChallenge;
use LM\AuthAbstractor\Challenge\U2fChallenge;
use LM\AuthAbstractor\Model\AuthenticationProcess;
use LM\AuthAbstractor\Factory\AuthenticationProcessFactory;
use LM\AuthAbstractor\Model\IAuthenticationCallback;
use LM\Common\Enum\Scalar;
use LM\Common\Model\ArrayObject;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use UnexpectedValueException;

/**
 * @todo Not really a decorator.
 */
class MiddlewareDecorator
{
    private $authProcessFactory;

    private $appConfig;

    private $config;

    private $router;

    private $secureSession;

    public function __construct(
        AuthenticationProcessFactory $authProcessFactory,
        Configuration $appConfig,
        AppConfigManager $config,
        RouterInterface $router,
        SecureSession $secureSession,
        U2fTokenRepository $u2fTokenRepo
    ) {
        $this->authProcessFactory = $authProcessFactory;
        $this->appConfig = $appConfig;
        $this->config = $config;
        $this->router = $router;
        $this->secureSession = $secureSession;
        $this->u2fTokenRepo = $u2fTokenRepo;
    }

    /**
     * @challenges is temp
     * @todo Should additionalData be stored within the authentication process?
     */
    public function createProcess(
        string $routeName,
        ArrayObject $challenges,
        ?string $username = null,
        ?int $maxNFailedAttempts = 3,
        array $additionalData = []
    ): Response {
        $challengesArray = $challenges->toArray(Scalar::_STR);
        $authProcess = $this
            ->authProcessFactory
            ->createProcess(
                $challengesArray,
                $maxNFailedAttempts,
                $username,
                $additionalData
            )
        ;
        $sid = $this
            ->secureSession
            ->storeObject(
                $authProcess,
                AuthenticationProcess::class
            )
        ;

        return new RedirectResponse($this
            ->router
            ->generate($routeName, [
                "sid" => $sid,
            ]))
        ;
    }

    public function updateProcess(
        Request $httpRequest,
        string $sid,
        IAuthenticationCallback $callback
    ) {
        $authKernel = new AuthenticationKernel($this->appConfig);
        $diactorosFactory = new DiactorosFactory();
        $httpFoundationFactory = new HttpFoundationFactory();
        $psrHttpRequest = $diactorosFactory->createRequest($httpRequest);
        $authProcess = $this->secureSession->getAndRemoveObject($sid, AuthenticationProcess::class);
        $authentifierResponse = $authKernel->processHttpRequest($psrHttpRequest, $authProcess, $callback);
        $this->secureSession->setObject($sid, $authentifierResponse->getProcess(), AuthenticationProcess::class);

        return $httpFoundationFactory->createResponse($authentifierResponse->getHttpResponse());
    }

    /**
     * @todo Delete.
     */
    public function getChallenges()
    {
        $pwdLoginAllowed = $this
            ->config
            ->getBoolSetting(Setting::ALLOW_PWD_LOGIN)
        ;
        $u2fLoginAllowed = $this
            ->config
            ->getBoolSetting(Setting::ALLOW_U2F_LOGIN)
        ;
        $nU2fKeys = $this
            ->config
            ->getIntSetting(Setting::N_U2F_KEYS_LOGIN)
        ;
        $u2fChallenges = [];
        for ($i = 0; $i < $nU2fKeys; $i++) {
            $u2fChallenges[] = U2fChallenge::class;
        }
        if ($pwdLoginAllowed && $u2fLoginAllowed) {
            return array_merge([CredentialChallenge::class], $u2fChallenges);
        } elseif ($u2fLoginAllowed) {
            return array_merge([ExistingUsernameChallenge::class], $u2fChallenges);
        } elseif ($pwdLoginAllowed) {
            return [
                CredentialChallenge::class,
            ];
        } else {
            throw new UnexpectedValueException();
        }
    }
}
