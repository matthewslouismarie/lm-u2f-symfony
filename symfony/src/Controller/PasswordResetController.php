<?php

namespace App\Controller;

use App\Model\AuthorizationRequest;
use App\Model\IAuthorizationRequest;
use App\Service\SecureSessionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class PasswordResetController extends AbstractController
{
    /**
     * @Route(
     *  "/not-authenticated/request-password-reset",
     *  name="request_password_reset",
     *  methods={"GET"})
     */
    public function requestPasswordReset(SecureSessionService $sSession)
    {
        $passwordResetRequest = new AuthorizationRequest(
            false,
            'confirm_password_request_reset',
            null
        );
        $passwordResetRequestSid = $sSession
            ->storeObject($passwordResetRequest, IAuthorizationRequest::class)
        ;
        $url = $this->generateUrl('u2f_authorization_uukp_u', array(
            'authorizationRequestSid' => $passwordResetRequestSid,
        ));
        return new RedirectResponse($url);
    }

    /**
     * @Route(
     *  "/not-authenticated/confirm-password-reset-request/{authorizationRequestSid}",
     *  name="confirm_password_request_reset",
     *  methods={"GET", "POST"})
     */
    public function confirmPasswordResetRequest()
    {

    }
}