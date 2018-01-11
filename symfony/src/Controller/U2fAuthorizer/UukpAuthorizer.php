<?php

namespace App\Controller\U2fAuthorizer;

use App\Form\UsernameType;
use App\FormModel\UsernameSubmission;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class UukpAuthorizer extends AbstractController
{
    /**
     * @todo Maybe should check passwordResetRequestSid?
     * 
     * @Route(
     *  "/all/u2f-authorisation/uukp/u/{passwordResetRequestSid}",
     *  name="u2f_authorization_uukp_u",
     *  methods={"GET", "POST"})
     */
    public function username(Request $request, string $passwordResetRequestSid)
    {
        $usernameSubmission = new UsernameSubmission();
        $usernameForm = $this
            ->createForm(UsernameType::class, $usernameSubmission)
        ;
        $usernameForm->handleRequest($request);
        if ($usernameForm->isSubmitted() && $usernameForm->isValid()) {
            $firstU2fUrl = $this
                ->generateUrl('u2f_authorization_uukp_u2f_key', array(
                    'passwordResetRequestSid' => $passwordResetRequestSid,
                ))
            ;
            return new RedirectResponse($firstU2fUrl);
        }
        return $this->render('u2f_authorization/uukp/username.html.twig', array(
            'form' => $usernameForm->createView(),
        ));
    }

    /**
     * @Route(
     *  "/all/u2f-authorisation/uukp/first-u2f-key/{passwordResetRequestSid}",
     *  name="u2f_authorization_uukp_u2f_key",
     *  methods={"GET", "POST"})
     */
    public function firstU2fKey()
    {
        
    }
}