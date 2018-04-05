<?php

namespace App\Service\Authentifier;

use App\Service\Authentifier\Configuration;
use App\Service\SecureSession;
use LM\Authentifier\Controller\AuthenticationKernel;
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

class MiddlewareDecorator
{
    private $authProcessFactory;

    private $config;

    private $router;

    private $secureSession;

    public function __construct(
        AuthenticationProcessFactory $authProcessFactory,
        Configuration $config,
        RouterInterface $router,
        SecureSession $secureSession)
    {
        $this->authProcessFactory = $authProcessFactory;
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
        $authKernel = new AuthenticationKernel($this->config);
        $diactorosFactory = new DiactorosFactory();
        $httpFoundationFactory = new HttpFoundationFactory();
        $psrHttpRequest = $diactorosFactory->createRequest($httpRequest);
        if (null === $sid) {
            $authProcess = $this
                ->authProcessFactory
                ->createAnonymousU2fProcess(
                    [
                        ExistingUsernameChallenge::class,
                        U2fChallenge::class,
                    ],
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
}
