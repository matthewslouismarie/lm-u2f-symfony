<?php

namespace App\Controller;

use App\Entity\U2fToken;
use App\Form\U2fRegistrationType;
use App\FormModel\U2fRegistrationSubmission;
use App\Model\AuthorizationRequest;
use App\Model\U2fRegistrationRequest;
use App\Service\SecureSession;
use App\Service\U2fRegistrationManager;
use App\SessionToken\HighSecurityAuthorizationToken;
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
    public function requestU2fTokenReset(SecureSession $sSession)
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
        $url = $this->generateUrl('high_security_authorization_username', array(
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
        SecureSession $sSession,
        U2fRegistrationManager $service,
        string $authorizationTokenSid)
    {
        $authorizationToken = $sSession
            ->getObject($authorizationTokenSid, HighSecurityAuthorizationToken::class)
        ;
        $challenge = $service->generate();
        $sid = $sSession->storeObject($challenge, U2fRegistrationRequest::class);
        $submission = new U2fRegistrationSubmission($sid);
        $form = $this->createForm(U2fRegistrationType::class, $submission);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $u2fToken = $service->getU2fTokenFromResponse(
                $submission->getU2fTokenResponse(),
                $this->getUser(),
                new DateTimeImmutable(),
                $sSession->getObject($submission->getRequestId(), RegisterRequest::class)
            );
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
            $om->persist($u2fToken);
            $om->flush();

            return $this->render('successful_u2f_token_reset.html.twig');
        }

        return $this->render('reset_u2f_token.html.twig', array(
            'form' => $form->createView(),
            'request_json' => $challenge->getRequestAsJson(),
            'sign_requests' => $challenge->getSignRequests(),
            'tmp' => $challenge,
        ));
    }
}
