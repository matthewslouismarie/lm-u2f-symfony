<?php

namespace App\Controller\U2fAuthorizer;

use App\Form\U2fLoginType;
use App\Form\UsernameType;
use App\FormModel\U2fLoginSubmission;
use App\FormModel\UsernameSubmission;
use App\Model\AuthorizationRequest;
use App\Model\IAuthorizationRequest;
use App\Service\AuthRequestService;
use App\Service\SecureSessionService;
use App\SessionToken\UukpAuthorizationToken;
use App\TransitingUserInput\UToU2fUserInput;
use App\TransitingUserInput\U2fToU2fUserInput;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class UukpAuthorizer extends AbstractController
{
    /**
     * @todo Maybe should check authorizationRequestSid?
     * 
     * @Route(
     *  "/all/u2f-authorisation/uukp/u/{authorizationRequestSid}",
     *  name="u2f_authorization_uukp_u",
     *  methods={"GET", "POST"})
     */
    public function username(
        Request $request,
        SecureSessionService $sSession,
        string $authorizationRequestSid)
    {
        $usernameSubmission = new UsernameSubmission();
        $usernameForm = $this
            ->createForm(UsernameType::class, $usernameSubmission)
        ;
        $usernameForm->handleRequest($request);
        if ($usernameForm->isSubmitted() && $usernameForm->isValid()) {
            $transitingUserInput = new UToU2fUserInput(
                $usernameSubmission->getUsername(),
                $sSession->getAndRemoveObject($authorizationRequestSid, IAuthorizationRequest::class))
            ;
            $transitingUserInputSid = $sSession
                ->storeObject($transitingUserInput, UToU2fUserInput::class);
            $firstU2fUrl = $this
                ->generateUrl('u2f_authorization_uukp_u2f_key', array(
                    'transitingUserInputSid' => $transitingUserInputSid,
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
     * @todo Remove username from form.
     * 
     * @Route(
     *  "/all/u2f-authorisation/uukp/first-u2f-key/{transitingUserInputSid}",
     *  name="u2f_authorization_uukp_u2f_key",
     *  methods={"GET", "POST"})
     */
    public function firstU2fKey(
        AuthRequestService $u2fAuthentication,
        Request $request,
        SecureSessionService $sSession,
        string $transitingUserInputSid)
    {
        $uToU2fUserInput = $sSession
            ->getObject(
                    $transitingUserInputSid,
                    UToU2fUserInput::class)
        ;
        $username = $uToU2fUserInput->getUsername();
        $u2fAuthenticationData = $u2fAuthentication->generate($username);
        $u2fAuthenticationSubmission = new U2fLoginSubmission(
            $uToU2fUserInput->getUsername(),
            null,
            $u2fAuthenticationData['auth_id']
        );
        $form = $this
            ->createForm(U2fLoginType::class, $u2fAuthenticationSubmission)
        ;
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $u2fTokenId = $u2fAuthentication->processResponse(
                $u2fAuthenticationSubmission->getU2fAuthenticationRequestId(),
                $u2fAuthenticationSubmission->getUsername(),
                $u2fAuthenticationSubmission->getU2fTokenResponse())
            ;
            $u2fToU2fUserInput = new U2fToU2fUserInput(
                $u2fAuthenticationSubmission,
                $u2fTokenId,
                $uToU2fUserInput)
            ;
            $sSession->remove($transitingUserInputSid);
            $u2fToU2fUserInputSid = $sSession
                ->storeObject($u2fToU2fUserInput, U2fToU2fUserInput::class)
            ;
            $url = $this->generateUrl('u2f_authorization_uukp_second_u2f_key', array(
                'userInputSid' => $u2fToU2fUserInputSid,
            ));
            return new RedirectResponse($url);
        } 
        return $this
            ->render('u2f_authorization/uukp/first_u2f_token.html.twig', array(
                'form' => $form->createView(),
                'sign_requests_json' => $u2fAuthenticationData['sign_requests_json'],
                'tmp' => $u2fAuthenticationData['tmp'],
            ))
        ;
    }

    /**
     * @Route(
     *  "/all/u2f-authorisation/uukp/u2f-key-2/{userInputSid}",
     *  name="u2f_authorization_uukp_second_u2f_key",
     *  methods={"GET", "POST"})
     */
    public function secondU2fKey(
        AuthRequestService $u2fAuthentication,
        Request $request,
        SecureSessionService $sSession,
        string $userInputSid)
    {
        $userInput = $sSession
            ->getObject($userInputSid, U2fToU2fUserInput::class)
        ;
        $username = $userInput
            ->getUToU2fUserInput()
            ->getUsername()
        ;
        $authorizationRequest = $userInput
            ->getUToU2fUserInput()
            ->getAuthorizationRequest()
        ;

        $u2fAuthenticationData = $u2fAuthentication
            ->generate($username, array($userInput->getUsedU2fTokenId()))
        ;

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
            $sSession->remove($userInputSid);
            $authorizationToken = new UukpAuthorizationToken($username);
            $authorizationTokenSid = $sSession
                ->storeObject($authorizationToken, UukpAuthorizationToken::class)
            ;
            $url = $this->generateUrl($authorizationRequest->getSuccessRoute(), array(
                'authorizationTokenSid' => $authorizationTokenSid,
            ));
            return new RedirectResponse($url);
        } 
        return $this
            ->render('u2f_authorization/uukp/second_u2f_token.html.twig', array(
                'form' => $form->createView(),
                'sign_requests_json' => $u2fAuthenticationData['sign_requests_json'],
            ))
        ;
    }
}