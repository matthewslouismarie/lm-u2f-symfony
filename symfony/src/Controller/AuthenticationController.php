<?php

namespace App\Controller;

use App\Form\LoginRequestType;
use App\FormModel\LoginRequest;
use App\Form\UserConfirmationType;
use App\Model\AuthorizationRequest;
use App\Service\SecureSession;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Service\SerializableStack;
use App\FormModel\CredentialAuthenticationSubmission;

class AuthenticationController extends AbstractController
{
    /**
     * @Route(
     *  "/not-authenticated/start-login",
     *  name="login_request",
     *  methods={"GET"})
     */
    public function startLogin(
        Request $request,
        SerializableStack $SerializableStack,
        SecureSession $sSession)
    {
        $loginRequest = new AuthorizationRequest(false, 'finalize_login', null);
        $sid = $SerializableStack->create($loginRequest);
        $url = $this->generateUrl('medium_security_credential', array(
            'SerializableStackSid' => $sid,
        ));

        return new RedirectResponse($url);
    }

    /**
     * @todo Have a better error handling.
     *
     * @Route(
     *  "/not-authenticated/finalise-login/{SerializableStackSid}",
     *  name="finalize_login",
     *  methods={"GET", "POST"})
     */
    public function finishLogin(
        Request $request,
        SecureSession $sSession,
        SerializableStack $SerializableStack,
        string $SerializableStackSid)
    {
        $credential = $SerializableStack->get(
            $SerializableStackSid,
            1,
            CredentialAuthenticationSubmission::class
        );
        $authorizationRequest = $SerializableStack->isValid($SerializableStackSid);

        $loginRequest = new LoginRequest($credential->getUsername());
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

        return $this->render('registration/logout.html.twig', array(
            'form' => $form->createView(),
        ));
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
