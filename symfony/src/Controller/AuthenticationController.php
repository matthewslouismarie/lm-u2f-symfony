<?php

namespace App\Controller;

use App\Callback\Authentifier\MemberAuthenticationCallback;
use App\Enum\Setting;
use App\Exception\AccessDeniedException;
use App\Form\LoginRequestType;
use App\FormModel\CredentialAuthenticationSubmission;
use App\FormModel\LoginRequest;
use App\Form\UserConfirmationType;
use App\Model\AuthorizationRequest;
use App\Model\BooleanObject;
use App\Model\GrantedAuthorization;
use App\Repository\U2fTokenRepository;
use App\Repository\MemberRepository;
use App\Security\MemberAuthenticator;
use App\Service\Authentifier\Configuration;
use App\Service\AuthenticationManager;
use App\Service\AppConfigManager;
use App\Service\SecureSession;
use Firehed\U2F\Registration;
use LM\Authentifier\Controller\AuthenticationKernel;
use LM\Authentifier\Enum\AuthenticationProcess\Status;
use LM\Authentifier\Factory\AuthenticationProcessFactory;
use LM\Authentifier\Model\AuthenticationProcess;
use LM\Authentifier\Model\DataManager;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use UnexpectedValueException;

class AuthenticationController extends AbstractController
{
    /**
     * @Route(
     *  "/not-authenticated/choose-authenticate",
     *  name="choose_authenticate")
     */
    public function chooseAuthentication(AppConfigManager $config)
    {
        if ($config->getBoolSetting(Setting::ALLOW_PWD_LOGIN)
        && $config->getBoolSetting(Setting::ALLOW_U2F_LOGIN)) {
            return $this->render('choose_authentication_method.html.twig');
        } elseif ($config->getBoolSetting(Setting::ALLOW_PWD_LOGIN)) {
            return new RedirectResponse($this->generateUrl('pwd_authenticate'));
        } elseif ($config->getBoolSetting(Setting::ALLOW_U2F_LOGIN)) {
            return new RedirectResponse($this->generateUrl('authenticate'));
        } else {
            return $this->render("messages/unspecified_error.html.twig");
        }
    }

    /**
     * @Route(
     *  "/not-authenticated/pwd-authenticate",
     *  name="pwd_authenticate",
     *  methods={"GET"})
     */
    public function pwdAuthenticate(
        AppConfigManager $config,
        AuthenticationManager $requestManager)
    {
        if (false === $config->getBoolSetting(Setting::ALLOW_PWD_LOGIN)) {
            return new RedirectResponse($this->generateUrl('choose_authenticate'));
        }
        $identityRequest = $requestManager->create(
            'authenticate',
            [
                'ic_credential',
                'authentication_processing',
            ])
        ;

        return new RedirectResponse($identityRequest->getUrl());
    }

    /**
     * @Route(
     *  "/not-authenticated/authenticate",
     *  name="authenticate",
     *  methods={"GET"})
     */
    public function authenticate(
        AppConfigManager $config,
        AuthenticationManager $requestManager)
    {
        if ($config->getBoolSetting(Setting::ALLOW_U2F_LOGIN)) {
            $identityRequest = $requestManager->create(
                'authenticate',
                [
                    'ic_username',
                    'ic_u2f',
                    'authentication_processing',
                ])
            ;
    
            return new RedirectResponse($identityRequest->getUrl());
        } else {
            return new RedirectResponse($this->generateUrl('pwd_authenticate'));            
        }
    }

    /**
     * @Route(
     *  "/not-authenticated/process-login/{sid}",
     *  name="authentication_processing")
     */
    public function processAuthentication()
    {
    }

    /**
     * @Route(
     *  "/not-authenticated/tmp-process-login/{sid}",
     *  name="tmp_authentication_processing")
     */
    public function tmpProcessAuthentication()
    {
        return $this->render("messages/success.html.twig", [
            "message" => "Yey",
            "pageTitle" => "Yey",
        ]);
    }

    /**
     * @Route(
     *  "/authenticated/post-login",
     *  name="post_authentication")
     */
    public function postAuthentication(SecureSession $secureSession)
    {
        try {
            $isJustLoggedIn = $secureSession
                ->getAndRemoveObject(
                    MemberAuthenticator::JUST_LOGGED_IN,
                    BooleanObject::class)
                ->toBoolean()
            ;
            if (true === $isJustLoggedIn) {
                return $this->render('messages/success.html.twig', [
                    "pageTitle" => "You are logged in",
                    "message" => "You successfully logged in.",
                ]);
            } else {
                throw new UnexpectedValueException();
            }

        } catch (UnexpectedValueException $e) {
            return $this->render("messages/unspecified_error.html.twig");
        }
    }

    /**
     * @Route(
     *  "/authenticated/logout",
     *  name="unauthenticate",
     *  methods={"GET", "POST"})
     */
    public function unauthenticate(Request $request)
    {
    }

    /**
     * @Route(
     *  "/authenticated/not-logged-out",
     *  name="not_logged_out",
     *  methods={"GET"})
     */
    public function notLoggedOut()
    {
        return $this->render('not_logged_out_error.html.twig');
    }

    /**
     * @todo Hard-coded username.
     *
     * @Route(
     *  "/all/authenticate/{sid}",
     *  name="authentication")
     */
    public function processRequest(
        ?string $sid = null,
        AuthenticationProcessFactory $authenticationProcessFactory,
        MemberAuthenticationCallback $callback,
        MemberRepository $memberRepo,
        SecureSession $secureSession,
        U2fTokenRepository $u2fRepo,
        Configuration $config,
        Request $httpRequest)
    {
        $authKernel = new AuthenticationKernel($config);
        $diactorosFactory = new DiactorosFactory();
        $httpFoundationFactory = new HttpFoundationFactory();
        $psrHttpRequest = $diactorosFactory->createRequest($httpRequest);
        if (null === $sid) {
            $newAuthProcess = $authenticationProcessFactory->createU2fProcess(
                "louis",
                $u2fRepo->getMemberRegistrations($memberRepo->getMember("louis")),
                $callback
            );
            $authentifierResponse = $authKernel->processHttpRequest($psrHttpRequest, $newAuthProcess);
            $sid = $secureSession->storeObject($newAuthProcess, AuthenticationProcess::class);

            return new RedirectResponse($this->generateUrl("authentication", [
                "sid" => $sid,
            ]));
        }

        $authRequest = $secureSession->getAndRemoveObject($sid, AuthenticationProcess::class);
        $authentifierResponse = $authKernel->processHttpRequest($psrHttpRequest, $authRequest);
        $secureSession->setObject($sid, $authentifierResponse->getProcess(), AuthenticationProcess::class);

        return $httpFoundationFactory->createResponse($authentifierResponse->getHttpResponse());
    }
}
