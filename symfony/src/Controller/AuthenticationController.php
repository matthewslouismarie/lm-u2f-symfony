<?php

namespace App\Controller;

use App\Form\U2fLoginType;
use App\Form\UserConfirmationType;
use App\Form\UsernameAndPasswordType;
use App\FormModel\U2fLoginSubmission;
use App\FormModel\UsernameAndPasswordSubmission;
use App\Model\AuthorizationRequest;
use App\Service\AuthRequestService;
use App\Service\SecureSessionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
        $request = new AuthorizationRequest(false, 'finish_login');
        $sessionId = $sSession->store($request);
        $url = $this->generateUrl('u2f_authorization_upuk_up', array(
            'sessionId' => $sessionId,
        ));
        return new RedirectResponse($url);
    }

    /**
     * @Route(
     *  "/not-authenticated/finish-login",
     *  name="finish_login",
     *  methods={"GET"})
     */
    public function finishLogin()
    {

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
     * @Route("/not-logged-out", name="not_logged_out", methods={"GET"})
     */
    public function notLoggedOut()
    {
        return $this->render('not_logged_out_error.html.twig');
    }
}