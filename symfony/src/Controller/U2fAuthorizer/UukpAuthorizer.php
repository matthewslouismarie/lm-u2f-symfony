<?php

namespace App\Controller\U2fAuthorizer;

use App\Form\UsernameType;
use App\FormModel\UsernameSubmission;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class UukpAuthorizer extends AbstractController
{
    /**
     * @Route(
     *  "/all/u2f-authorisation/uukp/u/{passwordResetRequestSid}",
     *  name="u2f_authorization_uukp_u",
     *  methods={"GET", "POST"})
     */
    public function username()
    {
        $usernameSubmission = new UsernameSubmission();
        $usernameForm = $this
            ->createForm(UsernameType::class, $usernameSubmission)
        ;
        return $this->render('u2f_authorization/uukp/username.html.twig', array(
            'form' => $usernameForm->createView(),
        ));
    }
}