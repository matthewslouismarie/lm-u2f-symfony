<?php

namespace App\Controller;

use App\DataStructure\TransitingDataManager;
use App\Entity\U2fToken;
use App\Enum\Setting;
use App\Form\UserConfirmationType;
use App\Repository\U2fTokenRepository;
use App\Service\AppConfigManager;
use App\Service\IdentityVerificationRequestManager;
use App\Service\SecureSession;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class U2fKeyManagementController extends AbstractController
{
    /**
     * @Route(
     *  "/authenticated/manage-u2f-keys",
     *  name="manage_u2f_keys")
     */
    public function manageU2fKeys(U2fTokenRepository $u2fTokenRepo)
    {
        $u2fKeys = $u2fTokenRepo->getU2fTokens($this->getUser());

        return $this->render('manage_u2f_keys.html.twig', [
            'u2f_keys' => $u2fKeys,
        ]);
    }

    /**
     * @Route(
     *  "/authenticated/confirm-u2f-key-reset/{u2fKeySlug}",
     *  name="confirm_u2f_key_reset")
     */
    public function confirmU2fKeyReset(
        string $u2fKeySlug,
        Request $httpRequest,
        IdentityVerificationRequestManager $idRequestManager)
    {
        $form = $this->createForm(UserConfirmationType::class);
        $form->handleRequest($httpRequest);
        if ($form->isSubmitted() && $form->isValid()) {
            
            $idRequest = $idRequestManager->create(
                'confirm_u2f_key_reset',
                [
                    'ic_u2f',
                    'reset_u2f_key',
                ],
                [
                    'u2fKeySlug' => $u2fKeySlug,
                ])
            ;

            return new RedirectResponse($idRequest->getUrl());
        }

        return $this->render('confirm_u2f_key_reset.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route(
     *  "/authenticated/reset-u2f-key/{sid}",
     *  name="reset_u2f_key")
     */
    public function resetU2fKey(
        string $sid,
        AppConfigManager $config,
        EntityManagerInterface $em,
        Request $httpRequest,
        IdentityVerificationRequestManager $idRequestManager,
        TokenStorageInterface $tokenStorage,
        U2fTokenRepository $u2fTokenRepo)
    {
        $tdm = $idRequestManager->achieveOperation($sid, 'reset_u2f_key');
        $u2fKeySlug = $idRequestManager->getAdditionalData($tdm)['u2fKeySlug'];
        $u2fTokenRepo->removeU2fToken($this->getUser(), $u2fKeySlug);

        $nU2fKeys = count($em
            ->getRepository(U2fToken::class)
            ->getU2fTokens($this->getUser())
        );
        $requiredNU2fKeys = $config
            ->getIntSetting(Setting::POST_AUTH_N_U2F_KEYS)
        ;

        return $this->render('u2f_key_removed.html.twig', [
            'u2fKeySlug' => $u2fKeySlug,
            'nU2fKeys' => $nU2fKeys,
            'requiredNU2fKeys' => $requiredNU2fKeys,
        ]);
    }
}
