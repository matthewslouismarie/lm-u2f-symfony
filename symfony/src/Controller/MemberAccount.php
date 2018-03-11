<?php

namespace App\Controller;

use App\Entity\U2fToken;
use App\Form\PasswordUpdateType;
use App\FormModel\PasswordUpdateSubmission;
use App\Service\IdentityCheck\RequestManager;
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
    public function memberAccount()
    {
        return $this->render('member_account.html.twig');
    }

    /**
     * @Route(
     *  "/change-password",
     *  name="change_password")
     */
    public function updatePassword(
        Request $request,
        RequestManager $requestManager)
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
        RequestManager $requestManager,
        UserPasswordEncoderInterface $encoder)
    {
        $requestManager->checkIdentityFromSid($sid);
        $hashedPassword = $encoder->encodePassword(
            $this->getUser(),
            $requestManager->getAdditionalData($sid)['new_password']
        );
        $this->getUser()->setPassword($hashedPassword);
        $em->persist($this->getUser());
        $em->flush();

        return $this->render('successful_password_update.html.twig');
    }
}
