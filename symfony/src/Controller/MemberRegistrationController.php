<?php

namespace App\Controller;

use App\DataStructure\TransitingDataManager;
use App\Factory\MemberFactory;
use App\Form\CredentialRegistrationType;
use App\Form\NewU2fRegistrationType;
use App\Form\UserConfirmationType;
use App\FormModel\CredentialRegistrationSubmission;
use App\FormModel\NewU2fRegistrationSubmission;
use App\Model\TransitingData;
use App\Service\SecureSession;
use App\Service\U2fRegistrationManager;
use App\Service\U2fService;
use DateTimeImmutable;
use Doctrine\Common\Persistence\ObjectManager;
use Firehed\U2F\RegisterResponse;
use Firehed\U2F\Registration;
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
    public function fetchStartPage(SecureSession $secureSession): Response
    {
        $tdm = new TransitingDataManager();
        $sid = $secureSession->storeObject($tdm, TransitingDataManager::class);
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
        SecureSession $secureSession,
        string $sid): Response
    {
        $tdm = $secureSession->getObject($sid, TransitingDataManager::class);
        $submission = new CredentialRegistrationSubmission();
        $form = $this->createForm(
            CredentialRegistrationType::class,
            $submission
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $secureSession->setObject(
                $sid,
                $tdm->add(new TransitingData(
                    'CredentialRegistration',
                    'member_registration',
                    $submission
                )),
                TransitingDataManager::class
            );

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
     * @Route(
     *  "/not-authenticated/register/u2f-key/{sid}",
     *  name="registration_u2f_key")
     */
    public function fetchU2fPage(
        MemberFactory $mf,
        Request $request,
        SecureSession $secureSession,
        U2fRegistrationManager $service,
        U2fService $u2fService,
        string $sid): Response
    {
        $tdm = $secureSession->getObject($sid, TransitingDataManager::class);
        $server = $u2fService->getServer();
        $u2fKeyNo = $tdm
            ->getBy('class', NewU2fRegistrationSubmission::class)
            ->getSize()
        ;
        $submission = new NewU2fRegistrationSubmission();
        $form = $this->createForm(NewU2fRegistrationType::class, $submission);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $registerRequest = $tdm
                ->getBy('key', 'U2fKeyRequest'.$u2fKeyNo)
                ->getOnlyValue()
                ->getValue()
            ;
            $registration = $server
                ->setRegisterRequest($registerRequest)
                ->register(
                    RegisterResponse::fromJson($submission->getU2fTokenResponse())
                )
            ;
            $secureSession->setObject(
                $sid,
                $tdm
                    ->add(new TransitingData(
                        'U2fKeySubmission'.$u2fKeyNo,
                        'registration_u2f_key',
                        $submission
                    ))
                    ->add(new TransitingData(
                        'U2fRegistration'.$u2fKeyNo,
                        'registration_u2f_key',
                        $registration
                    )),
                TransitingDataManager::class
            );
            if (self::N_U2F_KEYS === $u2fKeyNo + 1) {
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

        $registrations = $tdm
            ->getBy('class', Registration::class)
            ->toArray()
        ;
        $registerRequest = $service->generate($registrations);
        $secureSession->setObject(
            $sid,
            $tdm->add(new TransitingData(
                'U2fKeyRequest'.$u2fKeyNo,
                'registration_u2f_key',
                $registerRequest->getRequest()
            )),
            TransitingDataManager::class
        );

        return $this->render('registration/key.html.twig', [
            'form' => $form->createView(),
            'request_json' => $registerRequest->getRequestAsJson(),
            'sign_requests' => $registerRequest->getSignRequests(),
            'tmp' => $registerRequest->getRequest(),
        ]);
    }

    /**
     * @todo Move DB logic somewhere else.
     *
     * @Route(
     *  "/not-authenticated/registration/submit/{sid}",
     *  name="registration_submit")
     */
    public function submitRegistration(
        ObjectManager $om,
        MemberFactory $mf,
        string $sid,
        SecureSession $secureSession,
        U2fRegistrationManager $u2fRegistrationManager,
        Request $request): Response
    {
        $tdm = $secureSession->getObject($sid, TransitingDataManager::class);
        $form = $this->createForm(UserConfirmationType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $credential = $tdm
                ->getBy('class', CredentialRegistrationSubmission::class)
                ->getOnlyValue()
                ->getValue()
            ;
            $member = $mf->create(
                null,
                $credential->getUsername(),
                $credential->getPassword()
            );
            $om->persist($member);
            for ($i = 0; $i < self::N_U2F_KEYS; ++$i) {
                $submission = $tdm
                    ->getBy('key', 'U2fKeySubmission'.$i)
                    ->getOnlyValue()
                    ->getValue()
                ;
                $u2fToken = $u2fRegistrationManager->getU2fTokenFromResponse(
                    $submission->getU2fTokenResponse(),
                    $member,
                    new DateTimeImmutable(),
                    $tdm
                        ->getBy('key', 'U2fKeyRequest'.$i)
                        ->getOnlyValue()
                        ->getValue(),
                    $submission->getU2fKeyName()
                );
                $om->persist($u2fToken);
            }
            $om->flush();
            $secureSession->deleteObject($sid, TransitingDataManager::class);

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
     * @Route(
     *  "/not-authenticated/registration/reset/{sid}",
     *  name="registration_reset")
     */
    public function resetRegistration(
        Request $request,
        SecureSession $secureSession,
        string $sid): Response
    {
        $tdm = $secureSession->getObject($sid, TransitingDataManager::class);
        $form = $this->createForm(UserConfirmationType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $secureSession->deleteObject($sid, TransitingDataManager::class);

            return $this->render('registration/successful_reset.html.twig');
        }

        return $this->render('registration/reset.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
