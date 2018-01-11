<?php

namespace App\Controller\U2fAuthorizer;

use App\Form\U2fLoginType;
use App\Form\UsernameType;
use App\FormModel\U2fLoginSubmission;
use App\FormModel\UsernameSubmission;
use App\Service\AuthRequestService;
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
                    'username' => $usernameSubmission->getUsername(),
                ))
            ;
            return new RedirectResponse($firstU2fUrl);
        }
        return $this->render('u2f_authorization/uukp/username.html.twig', array(
            'form' => $usernameForm->createView(),
        ));
    }

    /**
     * @todo Check response.
     * @todo Rename passwordResetRequestSid to authorizationRequestSid.
     * 
     * @Route(
     *  "/all/u2f-authorisation/uukp/first-u2f-key/{passwordResetRequestSid}/{username}",
     *  name="u2f_authorization_uukp_u2f_key",
     *  methods={"GET", "POST"})
     */
    public function firstU2fKey(
        AuthRequestService $u2fAuthentication,
        Request $request,
        string $passwordResetRequestSid,
        string $username)
    {
        $u2fAuthenticationData = $u2fAuthentication->generate($username);
        $u2fAuthenticationSubmission = new U2fLoginSubmission(
            $username,
            null,
            $u2fAuthenticationData['auth_id']
        );
        $form = $this
            ->createForm(U2fLoginType::class, $u2fAuthenticationSubmission)
        ;
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $url = $this->generateUrl('u2f_authorization_uukp_second_u2f_key', array(
                'authorizationRequestSid' => $passwordResetRequestSid,
            ));
            return new RedirectResponse($url);
        } 
        return $this
            ->render('u2f_authorization/uukp/first_u2f_token.html.twig', array(
                'form' => $form->createView(),
                'sign_requests_json' => $u2fAuthenticationData['sign_requests_json'],
            ))
        ;
    }

    /**
     * @Route(
     *  "/all/u2f-authorisation/uukp/u2f-key-2/{authorizationRequestSid}",
     *  name="u2f_authorization_uukp_second_u2f_key",
     *  methods={"GET", "POST"})
     */
    public function secondU2fKey()
    {
    }
}