<?php

namespace App\Controller;

use App\Model\IAuthorizationRequest;
use App\Form\LoginRequestType;
use App\FormModel\LoginRequest;
use App\Form\UserConfirmationType;
use App\Model\AuthorizationRequest;
use App\Service\SecureSessionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class AuthenticationController extends AbstractController
{
    /**
     * @Route(
     *  "/not-authenticated/login",
     *  name="start_login",
     *  methods={"GET"})
     */
    public function startLogin(
        Request $request,
        SecureSessionService $sSession)
    {
        $request = new AuthorizationRequest(false, 'finish_login', null);
        $sessionId = $sSession->storeObject($request, IAuthorizationRequest::class);
        $url = $this->generateUrl('u2f_authorization_upuk_up', array(
            'sessionId' => $sessionId,
        ));

        return new RedirectResponse($url);
    }

    /**
     * @todo Have a better error handling.
     *
     * @Route(
     *  "/not-authenticated/finish-login/{authorizationRequestSid}",
     *  name="finish_login",
     *  methods={"GET", "POST"})
     */
    public function finishLogin(
        Request $request,
        SecureSessionService $sSession,
        string $authorizationRequestSid)
    {
        $authorizationRequest = $sSession
            ->getAndRemoveObject(
                $authorizationRequestSid,
                IAuthorizationRequest::class)
        ;
        if (!$authorizationRequest->isAccepted()) {
            return new Response('error');
        }

        $loginRequest = new LoginRequest($authorizationRequest->getUsername());
        $form = $this->createForm(LoginRequestType::class, $loginRequest);
        $form->handleRequest($request);

        return $this->render('finish_login.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route(
     *  "/authenticated/log-out",
     *  name="logout",
     *  methods={"GET", "POST"})
     */
    public function logout(Request $request)
    {
        $form = $this->createForm(UserConfirmationType::class);
        $form->handleRequest($request);

        return $this->render('tks/logout.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route(
     *  "/not-logged-out",
     *  name="not_logged_out",
     *  methods={"GET"})
     */
    public function notLoggedOut()
    {
        return $this->render('not_logged_out_error.html.twig');
    }
}
