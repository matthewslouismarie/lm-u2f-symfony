<?php

namespace App\Controller;

use App\Form\CredentialRegistrationType;
use App\FormModel\CredentialRegistrationSubmission;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function fetchRegistrationPage(): Response
    {
        $submission = new CredentialRegistrationSubmission();
        $form = $this->createForm(
            CredentialRegistrationType::class,
            $submission
        );
        return $this->render('registration/username_and_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}