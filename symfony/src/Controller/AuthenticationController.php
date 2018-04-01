<?php

namespace App\Controller;

use App\Enum\Setting;
use App\Exception\AccessDeniedException;
use App\Form\LoginRequestType;
use App\FormModel\CredentialAuthenticationSubmission;
use App\FormModel\LoginRequest;
use App\Form\UserConfirmationType;
use App\Model\AuthorizationRequest;
use App\Model\BooleanObject;
use App\Model\GrantedAuthorization;
use App\Security\MemberAuthenticator;
use App\Service\AuthenticationManager;
use App\Service\AppConfigManager;
use App\Service\SecureSession;
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
        if ($config->getBoolSetting(Setting::ALLOW_U2F_LOGIN)) {
            return $this->render('choose_authentication_method.html.twig');
        } else {
            return new RedirectResponse($this->generateUrl('pwd_authenticate'));
        }
    }

    /**
     * @Route(
     *  "/not-authenticated/pwd-authenticate",
     *  name="pwd_authenticate",
     *  methods={"GET"})
     */
    public function pwdAuthenticate(AuthenticationManager $requestManager)
    {
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
}
