<?php

namespace App\Service\Authentifier;

use App\Enum\Setting;
use App\Service\AppConfigManager;
use App\Service\Authentifier\Configuration;
use App\Service\SecureSession;
use LM\Authentifier\Controller\AuthenticationKernel;
use LM\Authentifier\Challenge\CredentialChallenge;
use LM\Authentifier\Challenge\ExistingUsernameChallenge;
use LM\Authentifier\Challenge\U2fChallenge;
use LM\Authentifier\Model\AuthenticationProcess;
use LM\Authentifier\Factory\AuthenticationProcessFactory;
use LM\Authentifier\Model\IAuthenticationCallback;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
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
        SecureSession $secureSession)
    {
        $this->authProcessFactory = $authProcessFactory;
        $this->appConfig = $appConfig;
        $this->config = $config;
        $this->router = $router;
        $this->secureSession = $secureSession;
    }

    public function processRequest(
        IAuthenticationCallback $callback,
        Request $httpRequest,
        string $routeName,
        ?string $sid = null)
    {
        $authKernel = new AuthenticationKernel($this->appConfig);
        $diactorosFactory = new DiactorosFactory();
        $httpFoundationFactory = new HttpFoundationFactory();
        $psrHttpRequest = $diactorosFactory->createRequest($httpRequest);
        if (null === $sid) {
            $authProcess = $this
                ->authProcessFactory
                ->createAnonymousU2fProcess(
                    $this->getChallenges(),
                    $callback)
            ;
            $sid = $this->secureSession->storeObject($authProcess, AuthenticationProcess::class);

            return new RedirectResponse($this
                ->router
                ->generate($routeName, [
                    "sid" => $sid,
                ]))
            ;
        } else {
            $authProcess = $this->secureSession->getAndRemoveObject($sid, AuthenticationProcess::class);
            $authentifierResponse = $authKernel->processHttpRequest($psrHttpRequest, $authProcess);
            $this->secureSession->setObject($sid, $authentifierResponse->getProcess(), AuthenticationProcess::class);

            return $httpFoundationFactory->createResponse($authentifierResponse->getHttpResponse());
        }
    }

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
