<?php

namespace App\Controller\U2fAuthorizer;

use App\DataStructure\TransitingDataManager;
use App\Form\U2fAuthenticationType;
use App\Form\ExistingUsernameType;
use App\FormModel\U2fAuthenticationSubmission;
use App\FormModel\ExistingUsernameSubmission;
use App\Model\IAuthorizationRequest;
use App\Model\Integer;
use App\Model\TransitingData;
use App\Service\U2fAuthenticationManager;
use App\Service\SecureSession;
use App\SessionToken\HighSecurityAuthorizationToken;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class HighSecurityAuthorizer extends AbstractController
{
    /**
     * @Route(
     *  "/all/u2f-authorisation/high-security/u/{authorizationRequestSid}",
     *  name="high_security_authorization_username",
     *  methods={"GET", "POST"})
     */
    public function username(
        Request $request,
        SecureSession $sSession,
        string $authorizationRequestSid)
    {
        $authorizationRequest = $sSession
            ->getObject($authorizationRequestSid, IAuthorizationRequest::class)
        ;
        if (null !== $authorizationRequest->getUsername()) {
            return $this
                ->redirectToFirstU2fKey(
                    $authorizationRequest,
                    $sSession,
                    $authorizationRequest->getUsername())
            ;
        }
        $ExistingUsernameSubmission = new ExistingUsernameSubmission();
        $usernameForm = $this
            ->createForm(ExistingUsernameType::class, $ExistingUsernameSubmission)
        ;
        $usernameForm->handleRequest($request);
        if ($usernameForm->isSubmitted() && $usernameForm->isValid()) {
            return $this
                ->redirectToFirstU2fKey(
                    $authorizationRequest,
                    $sSession,
                    $ExistingUsernameSubmission->getUsername()
                )
            ;
        }

        return $this->render('high_security_authorizer/username.html.twig', array(
            'form' => $usernameForm->createView(),
        ));
    }

    /**
     * @todo Check response.
     * @todo Remove username from form.
     *
     * @Route(
     *  "/all/u2f-authorisation/high-security/first-u2f-key/{sid}",
     *  name="high_security_authorization_u2f",
     *  methods={"GET", "POST"})
     */
    public function firstU2fKey(
        U2fAuthenticationManager $u2fAuthentication,
        Request $request,
        SecureSession $sSession,
        string $sid)
    {
        $tdm = $sSession->getObject($sid, TransitingDataManager::class);
        $username = $tdm
            ->getBy('key', 'username')
            ->getOnlyvalue()
            ->getValue()
            ->getUsername()
        ;

        $u2fAuthenticationData = $u2fAuthentication->generate($username);
        $u2fAuthenticationSubmission = new U2fAuthenticationSubmission(
            $username,
            null,
            $u2fAuthenticationData['auth_id']
        );
        $form = $this
            ->createForm(U2fAuthenticationType::class, $u2fAuthenticationSubmission)
        ;
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $u2fTokenId = $u2fAuthentication->processResponse(
                $u2fAuthenticationSubmission->getU2fAuthenticationRequestId(),
                $u2fAuthenticationSubmission->getUsername(),
                $u2fAuthenticationSubmission->getU2fTokenResponse())
            ;
            $sSession->setObject(
                $sid,
                $tdm
                    ->add(new TransitingData(
                        'u2fTokenId',
                        'high_security_authorization_u2f',
                        new Integer($u2fTokenId)
                    ))
                    ->add(new TransitingData(
                        'u2fAuthenticationSubmission',
                        'high_security_authorization_u2f',
                        $u2fAuthenticationSubmission
                    )),
                TransitingDataManager::class
            );
            $url = $this->generateUrl('high_security_authorization_u2f_2', array(
                'sid' => $sid,
            ));

            return new RedirectResponse($url);
        }

        return $this
            ->render('high_security_authorizer/first_u2f_token.html.twig', array(
                'form' => $form->createView(),
                'sign_requests_json' => $u2fAuthenticationData['sign_requests_json'],
                'tmp' => $u2fAuthenticationData['tmp'],
            ))
        ;
    }

    /**
     * @Route(
     *  "/all/u2f-authorisation/high-security/u2f-key-2/{sid}",
     *  name="high_security_authorization_u2f_2",
     *  methods={"GET", "POST"})
     */
    public function secondU2fKey(
        U2fAuthenticationManager $u2fAuthentication,
        Request $request,
        SecureSession $sSession,
        string $sid)
    {
        $tdm = $sSession->getObject($sid, TransitingDataManager::class);
        $username = $tdm
            ->getBy('key', 'username')
            ->getOnlyValue()
            ->getValue()
            ->getUsername()
        ;
        $authorizationRequest = $tdm
            ->getBy('key', 'authorizationRequest')
            ->getOnlyValue()
            ->getValue()
        ;
        $usedTokenId = $tdm
            ->getBy('key', 'u2fTokenId')
            ->getOnlyValue()
            ->getValue()
        ;

        $u2fAuthenticationData = $u2fAuthentication
            ->generate($username, [$usedTokenId->getInteger()])
        ;

        $u2fAuthenticationSubmission = new U2fAuthenticationSubmission(
            $username,
            null,
            $u2fAuthenticationData['auth_id']
        );
        $form = $this
            ->createForm(U2fAuthenticationType::class, $u2fAuthenticationSubmission)
        ;
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $sSession->deleteObject($sid, TransitingDataManager::class);
            $u2fTokenId = $u2fAuthentication->processResponse(
                $u2fAuthenticationSubmission->getU2fAuthenticationRequestId(),
                $username,
                $u2fAuthenticationSubmission->getU2fTokenResponse()
            );
            $authorizationToken = new HighSecurityAuthorizationToken(
                $username,
                $usedTokenId->getInteger(),
                $u2fTokenId)
            ;
            $authorizationTokenSid = $sSession
                ->storeObject($authorizationToken, HighSecurityAuthorizationToken::class)
            ;
            $url = $this->generateUrl($authorizationRequest->getSuccessRoute(), array(
                'authorizationTokenSid' => $authorizationTokenSid,
            ));

            return new RedirectResponse($url);
        }

        return $this
            ->render('high_security_authorizer/second_u2f_token.html.twig', array(
                'form' => $form->createView(),
                'sign_requests_json' => $u2fAuthenticationData['sign_requests_json'],
            ))
        ;
    }

    private function redirectToFirstU2fKey(
        IAuthorizationRequest $authorizationRequest,
        SecureSession $sSession,
        string $username): RedirectResponse
    {
        $tdm = new TransitingDataManager();

        $sid = $sSession->storeObject(
            $tdm
                ->add(new TransitingData(
                    'authorizationRequest',
                    'high_security_authorization_username',
                    $authorizationRequest
                ))
                ->add(new TransitingData(
                    'username',
                    'high_security_authorization_username',
                    new ExistingUsernameSubmission($username)
                )),
            TransitingDataManager::class
        );
        $url = $this
            ->generateUrl('high_security_authorization_u2f', [
                'sid' => $sid,
            ])
        ;

        return new RedirectResponse($url);
    }
}
