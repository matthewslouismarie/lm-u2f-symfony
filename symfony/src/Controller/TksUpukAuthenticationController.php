<?php

namespace App\Controller;

use App\Form\U2fLoginType;
use App\Form\UsernameAndPasswordType;
use App\FormModel\U2fLoginSubmission;
use App\FormModel\UsernameAndPasswordSubmission;
use App\Service\AuthRequestService;
use App\Service\SecureSessionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class TksUpukAuthenticationController extends AbstractController
{
    /**
     * @Route(
     *  "/tks-upuk/not-authenticated/authenticate/username-and-password",
     *  name="tks_upuk_up_authenticate",
     *  methods={"GET", "POST"})
     */
    public function upAuthenticate(
        Request $request,
        SecureSessionService $secureSession)
    {
        $submission = new UsernameAndPasswordSubmission();
        $form = $this->createForm(UsernameAndPasswordType::class, $submission);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $upSubmissionId = $secureSession->store($submission);
            $url = $this->generateUrl('tks_upuk_uk_authenticate', array(
                'up-submission-id' => $upSubmissionId,
            ));
            return new RedirectResponse($url);
        }
        return $this->render('tks/upuk/upuk_login.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @todo Check session variable.
     * 
     * @Route(
     *  "/tks-upuk/not-authenticated/authenticate/u2f-key",
     *  name="tks_upuk_uk_authenticate",
     *  methods={"GET", "POST"})
     */
    public function ukAuthenticate(
        AuthRequestService $auth,
        Request $request,
        SecureSessionService $secureSession)
    {
        $upSubmissionId = $request
            ->query
            ->get('up-submission-id');
        $upSubmission = $secureSession->getAndRemove($upSubmissionId);

        if (!is_a($upSubmission, UsernameAndPasswordSubmission::class)) {
            $url = $this->generateUrl('tks_upuk_up_authenticate');
            return new RedirectResponse($url);
        }
        $authData = $auth->generate($upSubmission->getUsername());
        $submission = new U2fLoginSubmission(
            $upSubmission->getUsername(),
            $upSubmission->getPassword(),
            null,
            $authData['auth_id']
        );
        $form = $this->createForm(U2fLoginType::class, $submission);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

        }
        return $this->render('tks/upuk/upuk_uk_authenticate.html.twig', array(
            'form' => $form->createView(),
            'sign_requests_json' => $authData['sign_requests_json'],
        ));
    }
}