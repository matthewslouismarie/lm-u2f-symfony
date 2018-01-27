<?php

namespace App\Controller\U2fAuthorizer;

use App\DataStructure\TransitingDataManager;
use App\Form\U2fAuthenticationType;
use App\Form\NewU2fAuthenticationType;
use App\Form\ExistingUsernameType;
use App\FormModel\ExistingUsernameSubmission;
use App\FormModel\U2fAuthenticationSubmission;
use App\FormModel\NewU2fAuthenticationSubmission;
use App\Model\IAuthorizationRequest;
use App\Model\Integer;
use App\Model\TransitingData;
use App\Service\U2fAuthenticationManager;
use App\Service\StatelessU2fAuthenticationManager;
use App\Service\SecureSession;
use App\SessionToken\HighSecurityAuthorizationToken;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class HighSecurityAuthorizer extends AbstractController
{
    /**
     * @todo Rename authorizationRequestSid to sid.
     * @todo Replace u by username in path.
     * @todo Use form to get username.
     *
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
     *  name="high_security_authorization_u2f_0",
     *  methods={"GET", "POST"})
     */
    public function firstU2fKey(
        StatelessU2fAuthenticationManager $u2fAuthentication,
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

        $u2fAuthenticationRequest = $u2fAuthentication->generate($username);
        $u2fAuthenticationSubmission = new NewU2fAuthenticationSubmission();
        $form = $this
            ->createForm(NewU2fAuthenticationType::class, $u2fAuthenticationSubmission)
        ;
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $u2fAuthenticationRequest = $tdm
                ->getBy('key', 'U2fAuthenticationRequest0')
                ->getOnlyValue()
                ->getValue()
            ;
            $u2fTokenId = $u2fAuthentication->processResponse(
                $u2fAuthenticationRequest,
                $username,
                $u2fAuthenticationSubmission->getU2fTokenResponse())
            ;
            $sSession->setObject(
                $sid,
                $tdm
                    ->add(new TransitingData(
                        'u2fTokenId',
                        'high_security_authorization_u2f_0',
                        new Integer($u2fTokenId)
                    ))
                    ->add(new TransitingData(
                        'u2fAuthenticationSubmission',
                        'high_security_authorization_u2f_0',
                        $u2fAuthenticationSubmission
                    )),
                TransitingDataManager::class
            );
            $url = $this->generateUrl('high_security_authorization_u2f_1', array(
                'sid' => $sid,
            ));

            return new RedirectResponse($url);
        }
        $sSession->setObject(
            $sid,
            $tdm
                ->filterBy('key', 'U2fAuthenticationRequest0')
                ->add(new TransitingData(
                    'U2fAuthenticationRequest0',
                    'high_security_authorization_u2f_0',
                    $u2fAuthenticationRequest
                )),
            TransitingDataManager::class
        );

        return $this
            ->render('high_security_authorizer/first_u2f_token.html.twig', array(
                'form' => $form->createView(),
                'sign_requests_json' => $u2fAuthenticationRequest->getJsonSignRequests(),
            ))
        ;
    }

    /**
     * @Route(
     *  "/all/u2f-authorisation/high-security/u2f-key-2/{sid}",
     *  name="high_security_authorization_u2f_1",
     *  methods={"GET", "POST"})
     */
    public function secondU2fKey(
        StatelessU2fAuthenticationManager $u2fAuthentication,
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

        $u2fAuthenticationRequest = $u2fAuthentication
            ->generate($username, [$usedTokenId->getInteger()])
        ;

        $u2fAuthenticationSubmission = new NewU2fAuthenticationSubmission();
        $form = $this
            ->createForm(
                NewU2fAuthenticationType::class,
                $u2fAuthenticationSubmission
            )
        ;
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $sSession->deleteObject($sid, TransitingDataManager::class);
            $u2fTokenId = $u2fAuthentication->processResponse(
                $tdm
                    ->getBy('key', 'U2fAuthenticationRequest1')
                    ->getOnlyValue()
                    ->getValue(),
                $username,
                $u2fAuthenticationSubmission->getU2fTokenResponse()
            );
            $authorizationToken = new HighSecurityAuthorizationToken(
                $username,
                $usedTokenId->getInteger(),
                $u2fTokenId
            );
            
            $authorizationTokenSid = $sSession
                ->storeObject($authorizationToken, HighSecurityAuthorizationToken::class)
            ;
            $url = $this->generateUrl($authorizationRequest->getSuccessRoute(), array(
                'authorizationTokenSid' => $authorizationTokenSid,
            ));

            return new RedirectResponse($url);
        }
        $sSession->setObject(
            $sid,
            $tdm
                ->filterBy('key', 'U2fAuthenticationRequest1')
                ->add(
                    new TransitingData(
                        'U2fAuthenticationRequest1',
                        'high_security_authorization_u2f_1',
                        $u2fAuthenticationRequest
                    )),
            TransitingDataManager::class
        );

        return $this
            ->render('high_security_authorizer/second_u2f_token.html.twig', array(
                'form' => $form->createView(),
                'sign_requests_json' => $u2fAuthenticationRequest->getJsonSignRequests(),
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
            ->generateUrl('high_security_authorization_u2f_0', [
                'sid' => $sid,
            ])
        ;

        return new RedirectResponse($url);
    }
}
