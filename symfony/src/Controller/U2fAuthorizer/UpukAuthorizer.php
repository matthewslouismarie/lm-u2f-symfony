<?php

namespace App\Controller\U2fAuthorizer;

use App\Entity\Member;
use App\Exception\NonexistentMemberException;
use App\Form\NewU2fAuthenticationType;
use App\Form\CredentialAuthenticationType;
use App\FormModel\NewU2fAuthenticationSubmission;
use App\FormModel\CredentialAuthenticationSubmission;
use App\FormModel\NewLoginRequest;
use App\Model\IAuthorizationRequest;
use App\Model\AuthorizationRequest;
use App\Service\StatelessU2fAuthenticationManager;
use App\Service\SecureSession;
use App\Service\SubmissionStack;
use Doctrine\Common\Persistence\ObjectManager;
use Firehed\U2F\SecurityException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * This class handles the authorisation of IAuthorizationRequest objects. UPUK
 * stands for Username, Password and U2F Key.
 */
class UpukAuthorizer extends AbstractController
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
     *  "/all/u2f-authorization/upuk/up/{submissionStackSid}",
     *  name="u2f_authorization_upuk_up",
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
            $newSid = $submissionStack->add($submissionStackSid, $upSubmission);
            $url = $this->generateUrl('u2f_authorization_upuk_uk', [
                'submissionStackSid' => $newSid,
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
     *
     * @Route(
     *  "/all/u2f-authorization/upuk/uk/{submissionStackSid}",
     *  name="u2f_authorization_upuk_uk",
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
        $u2fAuthenticationRequest = $auth->generate($credential->getUsername());
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

        return $this->render('u2f_authorization/upuk/uk_authentication.html.twig', array(
            'form' => $form->createView(),
            'sign_requests_json' => $u2fAuthenticationRequest->getJsonSignRequests(),
        ));
    }
}
