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
        string $sid): Response
    {
        $submission = new CredentialRegistrationSubmission();
        $form = $this->createForm(
            CredentialRegistrationType::class,
            $submission
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
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
     * @Route(
     *  "/not-authenticated/register/u2f-key/{sid}",
     *  name="registration_u2f_key")
     */
    public function fetchFirstU2fTokenPage(
        Request $request,
        SubmissionStack $stack,
        U2fRegistrationManager $service,
        string $sid): Response
    {
        $registerRequest = $service->generate();
        $stack->add($sid, $registerRequest->getRequest());

        $submission = new NewU2fRegistrationSubmission();
        $form = $this->createForm(NewU2fRegistrationType::class, $submission);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
        }

        return $this->render('registration/key.html.twig', [
            'form' => $form->createView(),
            'request_json' => $registerRequest->getRequestAsJson(),
            'sign_requests' => $registerRequest->getSignRequests(),
        ]);
    }
}
