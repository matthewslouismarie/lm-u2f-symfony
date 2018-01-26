<?php

namespace App\Controller;

use App\Factory\MemberFactory;
use App\Form\CredentialRegistrationType;
use App\Form\NewU2fRegistrationType;
use App\Form\UserConfirmationType;
use App\FormModel\CredentialRegistrationSubmission;
use App\FormModel\NewU2fRegistrationSubmission;
use App\Service\SubmissionStack;
use App\Service\U2fRegistrationManager;
use DateTimeImmutable;
use Doctrine\Common\Persistence\ObjectManager;
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
     *  "/not-authenticated/registration/start",
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
     * @todo What if the stack is modified in the meantime?
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
                    $this->generateUrl('registration_submit', [
                        'sid' => $sid,
                    ])
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
     * @todo Save the stack's array to a local variable and use only that when
     * reading from it.
     * @todo Move DB logic somewhere else.
     * @todo Create all from the same stack.
     *
     * @Route(
     *  "/not-authenticated/registration/submit/{sid}",
     *  name="registration_submit")
     */
    public function submitRegistration(
        ObjectManager $om,
        MemberFactory $mf,
        string $sid,
        SubmissionStack $stack,
        U2fRegistrationManager $u2fRegistrationManager,
        Request $request): Response
    {
        $form = $this->createForm(UserConfirmationType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $member = $mf->create(
                null,
                $stack->get($sid, 0)->getUsername(),
                $stack->get($sid, 0)->getPassword()
            );
            $om->persist($member);
            for ($i = 1; $i <= self::N_U2F_KEYS; ++$i) {
                $u2fToken = $u2fRegistrationManager->getU2fTokenFromResponse(
                    $stack->get($sid, $i * 2, NewU2fRegistrationSubmission::class)->getU2fTokenResponse(),
                    $member,
                    new DateTimeImmutable(),
                    $stack->get($sid, $i * 2 - 1)
                );
                $om->persist($u2fToken);
            }
            $om->flush();
            $stack->delete($sid);

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
     * @todo What if the stack is invalid or nonexistent when delete is called?
     *
     * @Route(
     *  "/not-authenticated/registration/reset/{sid}",
     *  name="registration_reset")
     */
    public function resetRegistration(
        Request $request,
        SubmissionStack $stack,
        string $sid): Response
    {
        $form = $this->createForm(UserConfirmationType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $stack->delete($sid);

            return $this->render('registration/successful_reset.html.twig');
        }

        return $this->render('registration/reset.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
