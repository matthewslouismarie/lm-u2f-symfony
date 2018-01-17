<?php

namespace App\Controller;

// use App\Model\IAuthorizationRequest;
// use App\Form\LoginRequestType;
// use App\FormModel\LoginRequest;
// use App\FormModel\NewLoginRequest;
// use App\Form\UserConfirmationType;
// use App\Model\AuthorizationRequest;
// use App\Service\SecureSession;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
// use Symfony\Component\Routing\Annotation\Route;
// use Symfony\Component\HttpFoundation\RedirectResponse;
// use Symfony\Component\HttpFoundation\Response;
// use Symfony\Component\HttpFoundation\Request;
// use App\Service\SubmissionStack;
// use App\FormModel\CredentialAuthenticationSubmission;


class NewAuthenticationController extends AbstractController
{
    // /**
    //  * @Route(
    //  *  "/not-authenticated/login",
    //  *  name="start_login",
    //  *  methods={"GET"})
    //  */
    // public function startLogin(
    //     Request $request,
    //     SubmissionStack $submissionStack,
    //     SecureSession $sSession)
    // {
    //     $loginRequest = new NewLoginRequest('finish_login');
    //     $sid = $submissionStack->create($loginRequest);
    //     $url = $this->generateUrl('medium_security_credential', array(
    //         'submissionStackSid' => $sid,
    //     ));

    //     return new RedirectResponse($url);
    // }

    // /**
    //  * @todo Have a better error handling.
    //  *
    //  * @Route(
    //  *  "/not-authenticated/finish-login/{submissionStackSid}",
    //  *  name="finish_login",
    //  *  methods={"GET", "POST"})
    //  */
    // public function finishLogin(
    //     Request $request,
    //     SecureSession $sSession,
    //     SubmissionStack $submissionStack,
    //     string $submissionStackSid)
    // {
    //     $credential = $submissionStack->get(
    //         $submissionStackSid,
    //         1,
    //         CredentialAuthenticationSubmission::class)
    //     ;
    //     $authorizationRequest = $submissionStack->isValid($submissionStackSid);

    //     $loginRequest = new LoginRequest($credential->getUsername());
    //     $form = $this->createForm(LoginRequestType::class, $loginRequest);
    //     $form->handleRequest($request);

    //     return $this->render('finish_login.html.twig', array(
    //         'form' => $form->createView(),
    //     ));
    // }

    // /**
    //  * @Route(
    //  *  "/authenticated/log-out",
    //  *  name="logout",
    //  *  methods={"GET", "POST"})
    //  */
    // public function logout(Request $request)
    // {
    //     $form = $this->createForm(UserConfirmationType::class);
    //     $form->handleRequest($request);

    //     return $this->render('registration/logout.html.twig', array(
    //         'form' => $form->createView(),
    //     ));
    // }

    // /**
    //  * @Route(
    //  *  "/authenticated/not-logged-out",
    //  *  name="not_logged_out",
    //  *  methods={"GET"})
    //  */
    // public function notLoggedOut()
    // {
    //     return $this->render('not_logged_out_error.html.twig');
    // }
}
