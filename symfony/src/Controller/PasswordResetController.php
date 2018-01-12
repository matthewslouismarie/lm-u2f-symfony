<?php

namespace App\Controller;

use App\Entity\Member;
use App\Factory\MemberFactory;
use App\Form\PasswordUpdateType;
use App\FormModel\PasswordUpdateSubmission;
use App\Model\AuthorizationRequest;
use App\Model\IAuthorizationRequest;
use App\Service\SecureSessionService;
use App\SessionToken\UukpAuthorizationToken;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PasswordResetController extends AbstractController
{
    /**
     * @Route(
     *  "/not-authenticated/request-password-reset",
     *  name="request_password_reset",
     *  methods={"GET"})
     */
    public function requestPasswordReset(SecureSessionService $sSession)
    {
        $passwordResetRequest = new AuthorizationRequest(
            false,
            'confirm_password_request_reset',
            null
        );
        $passwordResetRequestSid = $sSession
            ->storeObject($passwordResetRequest, IAuthorizationRequest::class)
        ;
        $url = $this->generateUrl('u2f_authorization_uukp_u', array(
            'authorizationRequestSid' => $passwordResetRequestSid,
        ));
        return new RedirectResponse($url);
    }

    /**
     * @Route(
     *  "/not-authenticated/confirm-password-reset-request/{authorizationTokenSid}",
     *  name="confirm_password_request_reset",
     *  methods={"GET", "POST"})
     */
    public function confirmPasswordResetRequest(
        MemberFactory $mf,
        ObjectManager $om,
        Request $request,
        SecureSessionService $sSession,
        string $authorizationTokenSid)
    {
        $authorizationToken = $sSession
            ->getObject($authorizationTokenSid, UukpAuthorizationToken::class)
        ;
        $submission = new PasswordUpdateSubmission();
        $form = $this->createForm(PasswordUpdateType::class, $submission);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $sSession->remove($authorizationTokenSid);
            $member = $om
                ->getRepository(Member::class)
                ->findOneBy(array(
                    'username' => $authorizationToken->getUsername(),
                ))
            ;
            $mf->setPassword($member, $submission->getPassword());
            $om->persist($member);
            $om->flush();
            return $this->render('successful_password_reset.html.twig');
        }
        return $this->render('reset_password.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}