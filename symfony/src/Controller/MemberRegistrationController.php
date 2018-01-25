<?php

namespace App\Controller;

use App\Form\CredentialRegistrationType;
use App\Form\NewU2fRegistrationType;
use App\FormModel\CredentialRegistrationSubmission;
use App\FormModel\NewU2fRegistrationSubmission;
use App\Service\SubmissionStack;
use App\Service\U2fRegistrationManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class MemberRegistrationController extends AbstractController
{
    const N_U2F_KEYS = 3;

    /**
     * @Route(
     *  "/not-authenticated/register",
     *  name="registration_start",
     *  methods={"GET"})
     */
    public function fetchStartPage(SubmissionStack $stack): Response
    {
        $sid = $stack->create();
        $url = $this->generateUrl('member_registration', [
            'sid' => $sid,
        ]);

        return new RedirectResponse($url);
    }

    /**
     * @Route(
     *  "/not-authenticated/register/{sid}",
     *  name="member_registration",
     *  methods={"GET", "POST"}
     *  )
     */
    public function fetchRegistrationPage(
        Request $request,
        SubmissionStack $stack,
        string $sid): Response
    {
        $submission = new CredentialRegistrationSubmission();
        $form = $this->createForm(
            CredentialRegistrationType::class,
            $submission
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $stack->add($sid, $submission);
            return new RedirectResponse($this
                ->generateUrl('registration_u2f_key', [
                    'sid' => $sid,
                ])
            );
        }

        return $this->render('registration/username_and_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @todo 1 is not very explicit.
     *
     * @Route(
     *  "/not-authenticated/register/u2f-key/{sid}",
     *  name="registration_u2f_key")
     */
    public function fetchU2fPage(
        Request $request,
        SubmissionStack $stack,
        U2fRegistrationManager $service,
        string $sid): Response
    {
        if (self::N_U2F_KEYS === $stack->getSize($sid) - 1) {
            return new RedirectResponse(
                $this->generateUrl('registration_success')
            );
        }

        $submission = new NewU2fRegistrationSubmission();
        $form = $this->createForm(NewU2fRegistrationType::class, $submission);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            return new RedirectResponse(
                $this->generateUrl('registration_u2f_key', [
                    'sid' => $sid,
                ])
            );
        }

        $registerRequest = $service->generate();
        $stack->add($sid, $registerRequest->getRequest());

        return $this->render('registration/key.html.twig', [
            'form' => $form->createView(),
            'request_json' => $registerRequest->getRequestAsJson(),
            'sign_requests' => $registerRequest->getSignRequests(),
            'tmp' => $registerRequest->getRequest(),
        ]);
    }

    /**
     * @Route(
     *  "/not-authenticated/registration/success",
     *  name="registration_success",
     *  methods={"GET"})
     */
    public function fetchSuccessPage()
    {
        return $this->render('registration/success.html.twig');
    }
}
