<?php

namespace App\Controller;

use App\Callback\Authentifier\U2fDeviceRemovalCallback;
use App\Entity\U2fToken;
use App\Enum\Setting;
use App\Form\UserConfirmationType;
use App\Repository\U2fTokenRepository;
use App\Service\AppConfigManager;
use App\Service\Authentifier\MiddlewareDecorator;
use App\Service\ChallengeSpecification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class U2fKeyManagementController extends AbstractController
{
    /**
     * @todo Access authorisation and access denied handling should be done
     * somewhere else.
     * @Route(
     *  "/authenticated/manage-u2f-keys",
     *  name="manage_u2f_keys")
     */
    public function manageU2fKeys(
        AppConfigManager $config,
        U2fTokenRepository $u2fTokenRepo
    ) {
        if (false === $config->getBoolSetting(Setting::ALLOW_MEMBER_TO_MANAGE_U2F_KEYS)) {
            return new RedirectResponse($this->generateUrl('member_account'));
        }
        $u2fKeys = $u2fTokenRepo->getU2fTokens($this->getUser());

        return $this->render('manage_u2f_keys.html.twig', [
            'u2f_keys' => $u2fKeys,
        ]);
    }

    /**
     * @Route(
     *  "/authenticated/confirm-u2f-device-removal/{u2fKeySlug}",
     *  name="confirm_u2f_key_reset")
     */
    public function confirmU2fDeviceRemoval(
        string $u2fKeySlug,
        ChallengeSpecification $cs,
        MiddlewareDecorator $decorator,
        EntityManagerInterface $em,
        Request $httpRequest
    ) {
        $form = $this->createForm(UserConfirmationType::class);

        $form->handleRequest($httpRequest);
        if ($form->isSubmitted() && $form->isValid()) {
            $u2fRegistration = $em
                ->getRepository(U2fToken::class)
                ->findOneBy([
                    'name' => $u2fKeySlug,
                ])
            ;
            $callback = new U2fDeviceRemovalCallback($u2fRegistration);
            return $decorator->createProcess(
                $callback,
                'remove_u2f_device',
                $cs->getChallenges($this->getUser()->getUsername()),
                $this->getUser()->getUsername()
            );
        }

        return $this->render('confirm_u2f_key_reset.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route(
     *  "/authenticated/remove-u2f-device/{sid}",
     *  name="remove_u2f_device")
     */
    public function removeU2fDevice(
        string $sid,
        EntityManagerInterface $em,
        MiddlewareDecorator $decorator,
        Request $httpRequest
    ) {
        return $decorator->updateProcess($httpRequest, $sid);
    }
}
