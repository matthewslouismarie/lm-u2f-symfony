<?php

namespace App\Controller\U2fAuthorizer;

use App\Entity\Member;
use App\Form\NewU2fAuthenticationType;
use App\Form\CredentialAuthenticationType;
use App\FormModel\NewU2fAuthenticationSubmission;
use App\FormModel\CredentialAuthenticationSubmission;
use App\FormModel\NewLoginRequest;
use App\FormModel\U2fAuthenticationRequest;
use App\Service\StatelessU2fAuthenticationManager;
use App\Service\SecureSession;
use App\Service\SubmissionStack;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @todo Routes shouldn't be accessible backwards.
 */
class MediumSecurityAuthorizer extends AbstractController
{
    public function __construct(
        ObjectManager $om,
        UserPasswordEncoderInterface $encoder)
    {
        $this->om = $om;
        $this->encoder = $encoder;
    }

    /**
     * @Route(
     *  "/all/u2f-authorization/medium-security/credential/{submissionStackSid}",
     *  name="medium_security_credential",
     *  methods={"GET", "POST"})
     */
    public function performCredentialAuthentication(
        Request $request,
        SecureSession $sSession,
        SubmissionStack $submissionStack,
        string $submissionStackSid)
    {
        $upSubmission = new CredentialAuthenticationSubmission();
        $form = $this->createForm(CredentialAuthenticationType::class, $upSubmission);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $submissionStack->add($submissionStackSid, $upSubmission);
            $url = $this->generateUrl('medium_security_u2f_authentication', [
                'submissionStackSid' => $submissionStackSid,
            ]);

            return new RedirectResponse($url);
        }

        return $this->render('registration/username_and_password.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @todo What if the member doesn't have U2F tokens?
     * @todo What if the authentication expires by the time the user accesses
     * the success route?
     * @todo What if the user's U2F tokens change during the validation?
     * @todo Delete submission stack.
     * @todo Add validation to form.
     * @todo Catch exceptions and display error page.
     *
     * @Route(
     *  "/all/u2f-authorization/medium-security/u2f/{submissionStackSid}",
     *  name="medium_security_u2f_authentication",
     *  methods={"GET", "POST"})
     */
    public function performU2fAuthentication(
        StatelessU2fAuthenticationManager $auth,
        Request $httpRequest,
        SubmissionStack $submissionStack,
        string $submissionStackSid)
    {
        $credential = $submissionStack->get(
            $submissionStackSid,
            1,
            CredentialAuthenticationSubmission::class)
        ;

        $submission = new NewU2fAuthenticationSubmission();
        $form = $this->createForm(NewU2fAuthenticationType::class, $submission);

        $form->handleRequest($httpRequest);
        if ($form->isSubmitted() && $form->isValid()) {
            $member = $this
                ->getDoctrine()
                ->getRepository(Member::class)
                ->getMember($credential->getUsername())
            ;
            $validPassword = $this
                ->getDoctrine()
                ->getRepository(Member::class)
                ->checkPassword($member, $credential->getPassword())
            ;
            $u2fAuthenticationRequest = $submissionStack->get(
                $submissionStackSid,
                2,
                U2fAuthenticationRequest::class
            );
            $auth->processResponse(
                $u2fAuthenticationRequest,
                $credential->getUsername(),
                $submission->getU2fTokenResponse()
            );
            
            // process submission stack
            // if everything goes well
            $loginRequest = $submissionStack->get(
                $submissionStackSid,
                0,
                NewLoginRequest::class)
            ;
            $url = $this->generateUrl(
                $loginRequest->getSuccessRoute(),
                [
                    'submissionStackSid' => $submissionStackSid,
                ])
            ;

            return new RedirectResponse($url);
        }
        $u2fAuthenticationRequest = $auth->generate($credential->getUsername());
        $submissionStack->set($submissionStackSid, 2, $u2fAuthenticationRequest);

        return $this->render('u2f_authorization/upuk/uk_authentication.html.twig', array(
            'form' => $form->createView(),
            'sign_requests_json' => $u2fAuthenticationRequest->getJsonSignRequests(),
        ));
    }
}