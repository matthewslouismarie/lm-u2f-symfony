<?php

namespace App\Controller;

use App\Model\AuthorizationRequest;
use App\Service\SecureSessionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;

class U2fTokenResetController extends AbstractController
{
    /**
     * @Route(
     *  "/authenticated/request-u2f-token-reset",
     *  name="request_u2f_token_reset",
     *  methods={"GET"})
     */
    public function requestU2fTokenReset(SecureSessionService $sSession)
    {
        $username = $this
            ->getUser()
            ->getUsername()
        ;
        $authorizationRequest = new AuthorizationRequest(
            false,
            'reset_u2f_token',
            $username)
        ;
        $authorizationRequestSid = $sSession
            ->storeObject($authorizationRequest, AuthorizationRequest::class)
        ;
        $url = $this->generateUrl('u2f_authorization_uukp_u', array(
            'authorizationRequestSid' => $authorizationRequestSid,
        ));
        return new RedirectResponse($url);
    }
}