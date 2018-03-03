<?php

namespace App\Controller;

use App\DataStructure\TransitingDataManager;
use App\Exception\AccessDeniedException;
use App\Form\LoginRequestType;
use App\FormModel\CredentialAuthenticationSubmission;
use App\FormModel\LoginRequest;
use App\Form\UserConfirmationType;
use App\Model\AuthorizationRequest;
use App\Model\GrantedAuthorization;
use App\Model\TransitingData;
use App\Model\ArrayObject;
use App\Service\SecureSession;
use App\Service\SerializableStack;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class AuthenticationController extends AbstractController
{
    /**
     * @Route(
     *  "/not-authenticated/authenticate",
     *  name="authenticate",
     *  methods={"GET"})
     */
    public function authenticate(
        Request $request,
        SerializableStack $SerializableStack,
        SecureSession $secureSession)
    {
        $tdm = (new TransitingDataManager())
            ->add(new TransitingData('checkers', 'initial_route', new ArrayObject(['ic_username', 'ic_u2f', 'authentication_processing'])))
        ;
        $sid = $secureSession->storeObject($tdm, TransitingDataManager::class);

        return new RedirectResponse($this->generateUrl('ic_initialization', [
            'sid' => $sid,
        ]));
    }

    /**
     * @Route(
     *  "/not-authenticated/process-login/{sid}",
     *  name="authentication_processing")
     */
    public function processAuthentication()
    {
    }

    /**
     * @Route(
     *  "/authenticated/successful-login",
     *  name="successful_authentication")
     */
    public function successfulAuthentication()
    {
        return $this->render('successful_authentication.html.twig');
    }

    /**
     * @Route(
     *  "/authenticated/logout",
     *  name="unauthenticate",
     *  methods={"GET", "POST"})
     */
    public function unauthenticate(Request $request)
    {
    }

    /**
     * @Route(
     *  "/authenticated/not-logged-out",
     *  name="not_logged_out",
     *  methods={"GET"})
     */
    public function notLoggedOut()
    {
        return $this->render('not_logged_out_error.html.twig');
    }
}
