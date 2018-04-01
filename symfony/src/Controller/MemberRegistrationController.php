<?php

namespace App\Controller;

use App\DataStructure\TransitingDataManager;
use App\Enum\Setting;
use App\Factory\MemberFactory;
use App\Form\CredentialRegistrationType;
use App\Form\NewU2fRegistrationType;
use App\Form\UserConfirmationType;
use App\FormModel\CredentialRegistrationSubmission;
use App\FormModel\NewU2fRegistrationSubmission;
use App\Model\TransitingData;
use App\Service\AppConfigManager;
use App\Service\SecureSession;
use App\Service\U2fRegistrationManager;
use App\Service\U2fService;
use DateTimeImmutable;
use Doctrine\Common\Persistence\ObjectManager;
use Firehed\U2F\ClientErrorException;
use Firehed\U2F\RegisterRequest;
use Firehed\U2F\RegisterResponse;
use Firehed\U2F\Registration;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use UnexpectedValueException;

class MemberRegistrationController extends AbstractController
{
    const U2F_REG_REQUEST_KEY = 'u2f_reg_request_key';

    private $nU2fKeys;

    public function __construct(AppConfigManager $appConfigManager)
    {
        $this->nU2fKeys = $appConfigManager
            ->getIntSetting(Setting::N_U2F_KEYS_REG)
        ;
    }

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
        AppConfigManager $config,
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
            ->getBy('class', Registration::class)
            ->getSize()
        ;
        if ($u2fKeyNo === $config->getIntSetting(Setting::N_U2F_KEYS_REG)) {
            return new RedirectResponse(
                $this->generateUrl('registration_submit', [
                    'sid' => $sid,
                ])
            );
        }

        $submission = new NewU2fRegistrationSubmission();
        $form = $this->createForm(NewU2fRegistrationType::class, $submission);
        $form->handleRequest($request);
        try {
            if ($form->isSubmitted() && $form->isValid()) {
                $registerRequest = $tdm
                    ->getBy('key', self::U2F_REG_REQUEST_KEY)
                    ->getOnlyValue()
                    ->getValue(RegisterRequest::class)
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
                            $request->get("_route"),
                            $submission
                        ))
                        ->add(new TransitingData(
                            'U2fRegistration'.$u2fKeyNo,
                            $request->get("_route"),
                            $registration
                        )),
                    TransitingDataManager::class
                );
                if ($this->nU2fKeys === $u2fKeyNo + 1) {
                    return new RedirectResponse(
                        $this->generateUrl('registration_submit', [
                            'sid' => $sid,
                        ])
                    );
                } else {
                    return new RedirectResponse(
                        $this->generateUrl($request->get("_route"), [
                            'sid' => $sid,
                        ])
                    );
                }
            }
        } catch (ClientErrorException $e) {
            $form->addError(new FormError("You already registered this U2F key"));
        }

        $registrations = $tdm
            ->getBy('class', Registration::class)
            ->toArray()
        ;
        $registerRequest = $service->generate($registrations);
        $secureSession->setObject(
            $sid,
            $tdm->replaceByKey(new TransitingData(
                self::U2F_REG_REQUEST_KEY,
                $request->get("_route"),
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
        try {
            $tdm = $secureSession->getObject($sid, TransitingDataManager::class);
            $form = $this->createForm(UserConfirmationType::class);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $credential = $tdm
                    ->getBy('class', CredentialRegistrationSubmission::class)
                    ->getOnlyValue()
                    ->getValue(CredentialRegistrationSubmission::class)
                ;
                $member = $mf->create(
                    null,
                    $credential->getUsername(),
                    $credential->getPassword(),
                    ['ROLE_USER']
                );
                $om->persist($member);
                for ($i = 0; $i < $this->nU2fKeys; ++$i) {
                    $submission = $tdm
                        ->getBy('key', 'U2fKeySubmission'.$i)
                        ->getOnlyValue()
                        ->getValue(NewU2fRegistrationSubmission::class)
                    ;
                    $u2fToken = $u2fRegistrationManager->getU2fTokenFromResponse(
                        $submission->getU2fTokenResponse(),
                        $member,
                        new DateTimeImmutable(),
                        $tdm
                            ->getBy('key', self::U2F_REG_REQUEST_KEY)
                            ->getOnlyValue()
                            ->getValue(RegisterRequest::class),
                        $submission->getU2fKeyName()
                    );
                    $om->persist($u2fToken);
                }
                $om->flush();
                $secureSession->deleteObject($sid, TransitingDataManager::class);

                return $this->render("messages/success.html.twig", [
                    "pageTitle" => "Account created successfully",
                    "message" => "Your account was successfully created!",
                ]);
            }

            return $this->render('registration/submit.html.twig', [
                'form' => $form->createView(),
            ]);
        } catch (UnexpectedValueException $e) {
            return $this->render("messages/unspecified_error.html.twig");
        }
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
