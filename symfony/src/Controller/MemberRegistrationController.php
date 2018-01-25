<?php

namespace App\Controller;

use App\Form\CredentialRegistrationType;
use App\Form\NewU2fRegistrationType;
use App\FormModel\CredentialRegistrationSubmission;
use App\FormModel\NewU2fRegistrationSubmission;
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
     *  name="member_registration",
     *  methods={"GET", "POST"}
     *  )
     */
    public function fetchRegistrationPage(Request $request): Response
    {
        $submission = new CredentialRegistrationSubmission();
        $form = $this->createForm(
            CredentialRegistrationType::class,
            $submission
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            return new RedirectResponse($this
                ->generateUrl('registration_first_u2f_key'))
            ;
        }

        return $this->render('registration/username_and_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route(
     *  "/not-authenticated/register/first-u2f-key",
     *  name="registration_first_u2f_key")
     */
    public function fetchFirstU2fTokenPage(
        U2fRegistrationManager $service): Response
    {
        $registerRequest = $service->generate();
        $submission = new NewU2fRegistrationSubmission();
        $form = $this->createForm(NewU2fRegistrationType::class, $submission);
        return $this->render('registration/key.html.twig', [
            'form' => $form->createView(),
            'request_json' => $registerRequest->getRequestAsJson(),
            'sign_requests' => $registerRequest->getSignRequests(),
        ]);
    }
}