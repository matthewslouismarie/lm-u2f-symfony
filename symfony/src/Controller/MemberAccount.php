<?php

namespace App\Controller;

use App\DataStructure\TransitingDataManager;
use App\Entity\U2fToken;
use App\Enum\Setting;
use App\Exception\IdentityChecker\ProcessedException;
use App\Form\PasswordUpdateType;
use App\Form\UserConfirmationType;
use App\FormModel\PasswordUpdateSubmission;
use App\Service\AppConfigManager;
use App\Service\AuthenticationManager;
use App\Service\SecureSession;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @todo Add Controller suffix.
 */
class MemberAccount extends AbstractController
{
    /**
     * @Route(
     *  "/authenticated/my-account",
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
     *  "/authenticated/change-password",
     *  name="change_password")
     */
    public function updatePassword(
        Request $request,
        AuthenticationManager $requestManager)
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
     *  "/authenticated/process-password-update/{sid}",
     *  name="process_password_update")
     */
    public function processPasswordUpdate(
        string $sid,
        EntityManagerInterface $em,
        AuthenticationManager $requestManager,
        SecureSession $secureSession,
        UserPasswordEncoderInterface $encoder)
    {
        try {
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
    
            return $this->render('messages/success.html.twig', [
                "pageTitle" => "Your password was updated",
                "message" => "Your password was successfully updated."
            ]);
        } catch (ProcessedException $e) {
            return $this->render("messages/unspecified_error.html.twig");
        }
    }

    /**
     * @Route(
     *  "/authenticated/my-account/delete-account",
     *  name="delete_account")
     */
    public function deleteAccount(
        AuthenticationManager $authenticationRequestManager,
        Request $httpRequest)
    {
        $form = $this->createForm(UserConfirmationType::class);

        $form->handleRequest($httpRequest);
        if ($form->isSubmitted() && $form->isValid()) {
            $request = $authenticationRequestManager->createHighSecurityAuthenticationRequest(
                'delete_account',
                'process_account_deletion')
            ;

            return new RedirectResponse($request->getUrl());
        }

        return $this->render('delete_account.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @todo Delete user account.
     * @todo Real logging out.
     *
     * @Route(
     *  "/authenticated/process-account-deletion/{sid}",
     *  name="process_account_deletion")
     */
    public function processAccountDeletion(
        string $sid,
        AuthenticationManager $authenticationRequestManager,
        TokenStorageInterface $tokenStorage)
    {
        $tdm = $authenticationRequestManager->achieveOperation($sid, 'process_account_deletion');
        $tokenStorage->setToken(null);

        return $this->render('successful_account_deletion.html.twig');
    }
}
