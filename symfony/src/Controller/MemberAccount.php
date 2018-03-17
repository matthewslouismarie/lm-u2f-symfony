<?php

namespace App\Controller;

use App\DataStructure\TransitingDataManager;
use App\Entity\U2fToken;
use App\Enum\Setting;
use App\Form\PasswordUpdateType;
use App\FormModel\PasswordUpdateSubmission;
use App\Service\AppConfigManager;
use App\Service\IdentityVerificationRequestManager;
use App\Service\SecureSession;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @todo Add Controller suffix.
 */
class MemberAccount extends AbstractController
{
    /**
     * @Route(
     *  "/my-account",
     *  name="member_account"
     * )
     */
    public function memberAccount(AppConfigManager $config)
    {
        $allowMemberToManageU2fKeys = $config->getBoolSetting(Setting::ALLOW_MEMBER_TO_MANAGE_U2F_KEYS);

        return $this->render('member_account.html.twig', [
            'allow_member_to_manage_u2f_keys' => $allowMemberToManageU2fKeys,            
        ]);
    }

    /**
     * @Route(
     *  "/change-password",
     *  name="change_password")
     */
    public function updatePassword(
        Request $request,
        IdentityVerificationRequestManager $requestManager)
    {
        $submission = new PasswordUpdateSubmission();
        $form = $this->createForm(PasswordUpdateType::class, $submission);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $identityRequest = $requestManager->create(
                'change_password',
                [
                    'ic_u2f',
                    'process_password_update',
                ],
                [
                    'new_password' => $submission->getPassword(),
                ])
            ;

            return new RedirectResponse($identityRequest->getUrl());
        }

        return $this->render('change_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route(
     *  "/process-password-update/{sid}",
     *  name="process_password_update")
     */
    public function processPasswordUpdate(
        string $sid,
        EntityManagerInterface $em,
        IdentityVerificationRequestManager $requestManager,
        SecureSession $secureSession,
        UserPasswordEncoderInterface $encoder)
    {
        $tdm = $secureSession->getObject($sid, TransitingDataManager::class);
        $requestManager->assertSuccessful($tdm);
        $requestManager->assertNotProcessed($tdm);
        $hashedPassword = $encoder->encodePassword(
            $this->getUser(),
            $requestManager->getAdditionalData($tdm)['new_password']
        );
        $this->getUser()->setPassword($hashedPassword);
        $em->persist($this->getUser());
        $em->flush();
        $secureSession->setObject(
            $sid,
            $requestManager->setAsProcessed($tdm, 'process_password_update'),
            TransitingDataManager::class
        );

        return $this->render('successful_password_update.html.twig');
    }
}
