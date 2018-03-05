<?php

namespace App\Controller;

use App\Form\UserConfirmationType;
use App\Repository\U2fTokenRepository;
use App\Service\IdentityCheck\RequestManager;
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
        $u2fKeys = $u2fTokenRepo->getU2fTokens($this->getUser()->getId());

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
        RequestManager $idRequestManager)
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
        Request $httpRequest,
        RequestManager $idRequestManager,
        TokenStorageInterface $tokenStorage,
        U2fTokenRepository $u2fTokenRepo)
    {
        $idRequestManager->checkIdentityFromSid($sid);
        $u2fKeySlug = $idRequestManager->getAdditionalData($sid)['u2fKeySlug'];
        $u2fTokenRepo->removeU2fToken($this->getUser(), $u2fKeySlug);

        $tokenStorage->setToken(null);

        return $this->render('u2f_key_removed.html.twig', [
            'u2fKeySlug' => $u2fKeySlug,
        ]);
    }
}
