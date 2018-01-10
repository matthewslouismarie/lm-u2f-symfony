<?php

namespace App\Controller\U2fAuthorizer;

use App\Form\U2fLoginType;
use App\Form\UsernameAndPasswordType;
use App\FormModel\U2fLoginSubmission;
use App\FormModel\UsernameAndPasswordSubmission;
use App\Model\IAuthorizationRequest;
use App\Model\AuthorizationRequest;
use App\Service\AuthRequestService;
use App\Service\SecureSessionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * This class handles the authorisation of IAuthorizationRequest objects. UPUK
 * stands for Username, Password and U2F Key.
 */
class UpukAuthorizer extends AbstractController
{
    /**
     * @todo Is all the good prefix for the route?
     * 
     * @Route(
     *  "/all/u2f-authorization/upuk/up/{sessionId}",
     *  name="u2f_authorization_upuk_up",
     *  methods={"GET", "POST"},
     *  requirements={"sessionId"=".+"})
     */
    public function upukUp(
        Request $request,
        SecureSessionService $sSession,
        string $sessionId)
    {
        $upSubmission = new UsernameAndPasswordSubmission();
        $form = $this->createForm(UsernameAndPasswordType::class, $upSubmission);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $upSubmissionId = $sSession->store($upSubmission);
            $url = $this->generateUrl('u2f_authorization_upuk_uk', array(
                'sessionId' => $sessionId,
                'upSubmissionId' => $upSubmissionId,
            ));
            return new RedirectResponse($url);
        }
        return $this->render('tks/username_and_password.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @todo What if the username doesn't exist, or doesn't have U2F tokens?
     * 
     * @Route(
     *  "/all/u2f-authorization/upuk/uk/{sessionId}/{upSubmissionId}",
     *  name="u2f_authorization_upuk_uk",
     *  methods={"GET", "POST"},
     *  requirements={"sessionId"=".+", "upSubmissionId"=".+"})
     */
    public function upukUk(
        AuthRequestService $auth,
        Request $request,
        SecureSessionService $sSession,
        string $sessionId,
        string $upSubmissionId)
    {
        $upSubmission = $sSession->get($upSubmissionId);
        if (!is_a($upSubmission, UsernameAndPasswordSubmission::class)) {
            return new Response('error');
        }
        $u2fData = $auth->generate($upSubmission->getUsername());
        $u2fSubmission = new U2fLoginSubmission(
            $upSubmission->getUsername(),
            $upSubmission->getPassword(),
            null,
            $u2fData['auth_id']);
        $form = $this->createForm(U2fLoginType::class, $u2fSubmission);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $action = $sSession->getAndRemove($sessionId);
            if (!$action instanceof IAuthorizationRequest) {
                return new Response('Sorry, an error happened');
            }
            $validatedAction = new AuthorizationRequest(
                true,
                $action->getSuccessRoute());
            $authorizationRequestSid = $sSession->store($validatedAction);
            $url = $this->generateUrl($action->getSuccessRoute(), array(
                'authorizationRequestSid' => $authorizationRequestSid,
            ));
            return new RedirectResponse($url);
        }
        return $this->render('u2f_authorization/upuk/uk_login.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}