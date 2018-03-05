<?php

namespace App\Controller;

use App\Exception\AccessDeniedException;
use App\Form\LoginRequestType;
use App\FormModel\CredentialAuthenticationSubmission;
use App\FormModel\LoginRequest;
use App\Form\UserConfirmationType;
use App\Model\AuthorizationRequest;
use App\Model\GrantedAuthorization;
use App\Service\IdentityCheck\RequestManager;
use App\Service\SecureSession;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class AuthenticationController extends AbstractController
{
    /**
     * @Route(
     *  "/not-authenticated/authenticate",
     *  name="authenticate",
     *  methods={"GET"})
     */
    public function authenticate(RequestManager $requestManager)
    {
        $identityRequest = $requestManager->create(
            'authenticate',
            [
                'ic_username',
                'ic_u2f',
                'authentication_processing',
            ])
        ;

        return new RedirectResponse($identityRequest->getUrl());
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
     *  "/authenticated/successful-login",
     *  name="successful_authentication")
     */
    public function successfulAuthentication()
    {
        return $this->render('successful_authentication.html.twig');
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
