<?php

namespace App\Controller;

use App\Entity\U2fToken;
use App\Form\U2fTokenRegistrationType;
use App\FormModel\U2fTokenRegistration;
use App\Model\AuthorizationRequest;
use App\Service\SecureSessionService;
use App\Service\U2fTokenRegistrationService;
use App\SessionToken\UukpAuthorizationToken;
use DateTimeImmutable;
use Doctrine\Common\Persistence\ObjectManager;
use Firehed\U2F\RegisterRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class U2fTokenResetController extends AbstractController
{
    /**
     * @Route(
     *  "/authenticated/request-u2f-token-reset",
     *  name="request_u2f_token_reset",
     *  methods={"GET"})
     */
    public function requestU2fTokenReset(SecureSessionService $sSession)
    {
        $username = $this
            ->getUser()
            ->getUsername()
        ;
        $authorizationRequest = new AuthorizationRequest(
            false,
            'reset_u2f_token',
            $username)
        ;
        $authorizationRequestSid = $sSession
            ->storeObject($authorizationRequest, AuthorizationRequest::class)
        ;
        $url = $this->generateUrl('u2f_authorization_uukp_u', array(
            'authorizationRequestSid' => $authorizationRequestSid,
        ));

        return new RedirectResponse($url);
    }

    /**
     * @Route(
     *  "/authenticated/reset-u2f-token/{authorizationTokenSid}",
     *  name="reset_u2f_token",
     *  methods={"GET", "POST"})
     */
    
    public function resetU2fToken(
        ObjectManager $om,
        Request $request,
        SecureSessionService $sSession,
        U2fTokenRegistrationService $service,
        string $authorizationTokenSid)
    {
        $authorizationToken = $sSession
            ->getObject($authorizationTokenSid, UukpAuthorizationToken::class)
        ;
        $challenge = $service->generate();
        $submission = new U2fTokenRegistration($challenge['request_id']);
        $form = $this->createForm(U2fTokenRegistrationType::class, $submission);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $service->processResponse($submission->getU2fTokenResponse(), $this->getUser(), new DateTimeImmutable(), $submission->getRequestId());
            $u2fTokenToDelete = $om
                ->getRepository(U2fToken::class)
                ->getExcept(
                    $this->getUser(),
                    array(
                        $authorizationToken->getFirstU2fTokenUsed(),
                        $authorizationToken->getSecondU2fTokenUsed(),
                    ))
            ;
            $om->remove($u2fTokenToDelete[0]);
            $om->flush();
        }

        return $this->render('reset_u2f_token.html.twig', array(
            'form' => $form->createView(),
            'request_json' => $challenge['request_json'],
            'sign_requests' => $challenge['sign_requests'],
            'tmp' => $sSession->getObject($challenge['request_id'], RegisterRequest::class),
        ));
    }
}
