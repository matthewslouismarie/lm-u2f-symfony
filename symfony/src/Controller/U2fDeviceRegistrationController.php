<?php

namespace App\Controller;

use App\DataStructure\TransitingDataManager;
use App\Entity\U2fToken;
use App\Enum\Setting;
use App\Form\NewU2fRegistrationType;
use App\FormModel\NewU2fRegistrationSubmission;
use App\Model\TransitingData;
use App\Service\AppConfigManager;
use App\Service\SecureSession;
use App\Service\U2fRegistrationManager;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Firehed\U2F\ClientErrorException;
use Firehed\U2F\RegisterRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class U2fDeviceRegistrationController extends AbstractController
{
    /**
     * @Route(
     *  "/authenticated/register-u2f-device/{sid}",
     *  name="register_u2f_device")
     */
    public function registerU2fDevice(
        string $sid = null,
        AppConfigManager $config,
        SecureSession $secureSession,
        EntityManagerInterface $em,
        Request $httpRequest,
        U2fRegistrationManager $u2fRegistrationManager
    ) {
        if (false === $config->getBoolSetting(Setting::ALLOW_MEMBER_TO_MANAGE_U2F_KEYS)) {
            return new RedirectResponse($this->generateUrl('member_account'));
        }
        if (null === $sid) {
            $sid = $secureSession->storeObject(
                new TransitingDataManager(),
                TransitingDataManager::class
            );

            return new RedirectResponse(
                $this->generateUrl($httpRequest->get('_route'), ['sid' => $sid])
            );
        } else {
            try {
                $tdm = $secureSession->getObject($sid, TransitingDataManager::class);

                $submission = new NewU2fRegistrationSubmission();
                $form = $this->createForm(NewU2fRegistrationType::class, $submission);
                $form->handleRequest($httpRequest);
                if ($form->isSubmitted() && $form->isValid()) {
                    $newU2fToken = $u2fRegistrationManager->getU2fTokenFromResponse(
                        $submission->getU2fTokenResponse(),
                        $this->getUser(),
                        new DateTimeImmutable(),
                        $tdm
                            ->getBy('key', 'u2f_registration_request')
                            ->getOnlyValue()
                            ->getValue(RegisterRequest::class),
                        $submission->getU2fKeyName()
                    );
                    $em->persist($newU2fToken);
                    $em->flush();
                    $memberU2fTokens = $em
                        ->getRepository(U2fToken::class)
                        ->findBy(['member' => $this->getUser()])
                    ;
        
                    return $this->render('key_added.html.twig', [
                        'nU2fTokens' => count($memberU2fTokens),
                        'nU2fTokensRequired' => $config->getIntSetting(Setting::N_U2F_KEYS_POST_AUTH),
                    ]);
                }
                $registrations = $em
                    ->getRepository(U2fToken::class)
                    ->getMemberRegistrations(
                        $this->getUser()
                    )
                ;
                $u2fRegistrationRequest = $u2fRegistrationManager->generate(
                    $registrations
                )
                ;
                $secureSession->setObject(
                    $sid,
                    $tdm
                        ->replaceByKey(new TransitingData(
                            'u2f_registration_request',
                            'add_u2f_key',
                            $u2fRegistrationRequest->getRequest()
                        )),
                    TransitingDataManager::class
                );
        
                return $this->render('add_u2f_key.html.twig', [
                    'form' => $form->createView(),
                    'request_json' => $u2fRegistrationRequest->getRequestAsJson(),
                    'sign_requests' => $u2fRegistrationRequest->getSignRequests(),
                ]);
            } catch (ClientErrorException $e) {
                return $this->render('registration/errors/u2f_timeout.html.twig', [
                    'sid' => $sid,
                ]);
            }
        }
    }
}
