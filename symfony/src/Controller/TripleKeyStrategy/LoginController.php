<?php

namespace App\Controller\TripleKeyStrategy;

use App\Form\U2fLoginType;
use App\Form\UsernameAndPasswordType;
use App\FormModel\U2fLoginSubmission;
use App\FormModel\UsernameAndPasswordSubmission;
use App\Service\AuthRequestService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class LoginController extends AbstractController
{
    /**
     * @Route(
     *  "/tks/login",
     *  name="tks_login_username_and_password",
     *  methods={"GET"})
     */
    public function usernameAndPassword(Request $request)
    {
        $upSubmission = new UsernameAndPasswordSubmission();
        $upForm = $this
            ->createForm(UsernameAndPasswordType::class, $upSubmission);
        return $this->render('tks/login/username_and_password.html.twig', array(
            'form' => $upForm->createView(),
        ));
    }

    /**
     * @todo Fix error messages.
     * @Route(
     *  "/tks/login",
     *  name="tks_login_u2f_authentication",
     *  methods={"POST"})
     */
    public function u2fAuthentication(
        AuthRequestService $u2fAuth,
        Request $request)
    {
        $upSubmission = new UsernameAndPasswordSubmission();
        $upForm = $this->createForm(
            UsernameAndPasswordType::class,
            $upSubmission);
        $upForm->handleRequest($request);

        if ($upForm->isSubmitted() && $upForm->isValid()) {
            $data = $u2fAuth->generate($upSubmission->getUsername());
            $submission = new U2fLoginSubmission();
            $submission->username = $upSubmission->getUsername();
            $submission->password = $upSubmission->getPassword();
            $submission->requestId = $data['auth_id'];
            $form = $this->createForm(U2fLoginType::class, $submission, array(
                'action' => $this->generateUrl('tks_login_validate'),
            ));

            return $this
                ->render('/tks/login/u2f_authentication.html.twig', array(
                    'form' => $form->createView(),
                    'sign_requests_json' => $data['sign_requests_json'],
                    'tmp' => $data['tmp'],
            ));
        } else {
            return $this->render('tks/login/username_and_password.html.twig', array(
                'form' => $upForm->createView(),
            ));
        }
    }

    /**
     * @Route(
     *  "/tks/login/validate",
     *  name="tks_login_validate",
     *  methods={"POST"})
     */
    public function validateLogin()
    {

    }
}