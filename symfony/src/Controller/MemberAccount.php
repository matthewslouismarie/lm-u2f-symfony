<?php

namespace App\Controller;

use App\Callback\Authentifier\PasswordUpdateCallback;
use App\Callback\Authentifier\AccountDeletionCallback;
use App\Enum\Setting;
use App\Form\PasswordUpdateType;
use App\Form\UserConfirmationType;
use App\FormModel\PasswordUpdateSubmission;
use App\Service\AppConfigManager;
use App\Service\Authentifier\MiddlewareDecorator;
use App\Service\ChallengeSpecification;
use LM\Authentifier\Challenge\CredentialChallenge;
use LM\Authentifier\Challenge\PasswordChallenge;
use LM\Authentifier\Challenge\PasswordUpdateChallenge;
use LM\Common\Enum\Scalar;
use LM\Common\Model\ArrayObject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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
     *  "/authenticated/change-password/{sid}",
     *  name="password_update")
     */
    public function updatePassword(
        string $sid = null,
        PasswordUpdateCallback $callback,
        ChallengeSpecification $cs,
        MiddlewareDecorator $decorator,
        Request $httpRequest
    ) {
        if (null === $sid) {
            return $decorator->createProcess(
                $callback,
                $httpRequest->get('_route'),
                $cs->getChallenges(
                    $this->getUser()->getUsername(),
                    [],
                    [
                        PasswordUpdateChallenge::class,
                    ]                        
                ),
                $this->getUser()->getUsername(),
                20
            )
            ;
        } else {
            return $decorator->updateProcess($httpRequest, $sid);
        }
    }

    /**
     * @Route(
     *  "/authenticated/my-account/delete-account/{sid}",
     *  name="delete_account")
     */
    public function deleteAccount(
        string $sid = null,
        ChallengeSpecification $cs,
        Request $httpRequest,
        MiddlewareDecorator $decorator
    ) {
        if (null === $sid) {
            $form = $this->createForm(UserConfirmationType::class);

            $form->handleRequest($httpRequest);
            if ($form->isSubmitted() && $form->isValid()) {
                $callback = new AccountDeletionCallback($this->getUser());

                return $decorator->createProcess(
                    $callback,
                    $httpRequest->get('_route'),
                    $cs->getChallenges($this->getUser()->getUsername()),
                    $this->getUser()->getUsername()
                );
            }

            return $this->render('delete_account.html.twig', [
                'form' => $form->createView(),
            ]);
        } else {
            return $decorator->updateProcess($httpRequest, $sid);
        }
    }
}
