<?php

namespace App\Controller;

use App\Form\CredentialRegistrationType;
use App\FormModel\CredentialRegistrationSubmission;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
    public function fetchFirstU2fTokenPage(): Response
    {
        return new Response();
    }
}