<?php

namespace App\Controller;

use App\Entity\Member;
use App\Factory\MemberFactory;
use App\Form\CredentialRegistrationType;
use App\Form\NewU2fRegistrationType;
use App\Form\UserConfirmationType;
use App\FormModel\CredentialRegistrationSubmission;
use App\FormModel\NewU2fRegistrationSubmission;
use App\Service\SubmissionStack;
use App\Service\U2fRegistrationManager;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class MemberRegistrationController extends AbstractController
{
    const N_U2F_KEYS = 3;

    /**
     * @Route(
     *  "/not-authenticated/register",
     *  name="registration_start",
     *  methods={"GET"})
     */
    public function fetchStartPage(SubmissionStack $stack): Response
    {
        $sid = $stack->create();
        $url = $this->generateUrl('member_registration', [
            'sid' => $sid,
        ]);

        return new RedirectResponse($url);
    }

    /**
     * @Route(
     *  "/not-authenticated/register/{sid}",
     *  name="member_registration",
     *  methods={"GET", "POST"}
     *  )
     */
    public function fetchRegistrationPage(
        Request $request,
        SubmissionStack $stack,
        string $sid): Response
    {
        $submission = new CredentialRegistrationSubmission();
        $form = $this->createForm(
            CredentialRegistrationType::class,
            $submission
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $stack->add($sid, $submission);

            return new RedirectResponse($this
                ->generateUrl('registration_u2f_key', [
                    'sid' => $sid,
                ])
            );
        }

        return $this->render('registration/username_and_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @todo 1 is not very explicit.
     * @todo Move processing in another controller.
     *
     * @Route(
     *  "/not-authenticated/register/u2f-key/{sid}",
     *  name="registration_u2f_key")
     */
    public function fetchU2fPage(
        MemberFactory $mf,
        Request $request,
        SubmissionStack $stack,
        U2fRegistrationManager $service,
        string $sid): Response
    {
        $submission = new NewU2fRegistrationSubmission();
        $form = $this->createForm(NewU2fRegistrationType::class, $submission);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $stack->add($sid, $submission);
            if (self::N_U2F_KEYS * 2 === $stack->getSize($sid) - 1) {
                return new RedirectResponse(
                    $this->generateUrl('registration_submit')
                );
            } else {
                return new RedirectResponse(
                    $this->generateUrl('registration_u2f_key', [
                        'sid' => $sid,
                    ])
                );
            }
        }

        $registerRequest = $service->generate();
        $stack->add($sid, $registerRequest->getRequest());

        return $this->render('registration/key.html.twig', [
            'form' => $form->createView(),
            'request_json' => $registerRequest->getRequestAsJson(),
            'sign_requests' => $registerRequest->getSignRequests(),
            'tmp' => $registerRequest->getRequest(),
        ]);
    }

    /**
     * @Route(
     *  "/not-authenticated/registration/submit",
     *  name="registration_submit")
     */
    public function submitRegistration(Request $request): Response
    {
        $form = $this->createForm(UserConfirmationType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            return new RedirectResponse(
                $this->generateUrl('registration_success')
            );
        }

        return $this->render('registration/submit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route(
     *  "/not-authenticated/registration/success",
     *  name="registration_success",
     *  methods={"GET"})
     */
    public function fetchSuccessPage()
    {
        return $this->render('registration/success.html.twig');
    }

    /**
     * @todo Should be given the array itself as parameter.
     * @todo Should be a route.
     * @todo EntityManager with DI.
     */
    private function processRegistration(
        MemberFactory $mf,
        string $sid,
        SubmissionStack $stack,
        U2fRegistrationManager $u2fRegistrationManager)
    {
        $member = $mf->create(
            null,
            $stack->get($sid, 0)->getUsername(),
            $stack->get($sid, 0)->getPassword()
        );
        $em = $this->getDoctrine()->getManager();
        $em->persist($member);
        $em->flush();
        $u2fRegistrationManager->getU2fTokenFromResponse(
            $stack->get($sid, 2)->getU2fTokenResponse(),
            $member,
            new DateTimeImmutable(),
            $stack->get($sid, 1)->getRequest()
        );
    }
}
