<?php

namespace App\Controller;

use App\Enum\Setting;
use App\Form\SecurityStrategyType;
use App\FormModel\SecurityStrategySubmission;
use App\Form\U2fConfigType;
use App\FormModel\U2fConfigSubmission;
use App\Service\AppConfigManager;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AdminDashboardController extends AbstractController
{
    /**
     * @Route(
     *  "/admin",
     *  name="admin")
     */
    public function getDashboard()
    {
        return $this->render('admin/admin_overview.html.twig');
    }

    /**
     * @Route(
     *  "/admin/registration",
     *  name="admin_registration")
     */
    public function getRegistrationPanel(
        AppConfigManager $appConfigManager,
        Request $httpRequest)
    {
        $submission = new U2fConfigSubmission(
            $appConfigManager->getBoolSetting(Setting::ALLOW_U2F_LOGIN),
            $appConfigManager->getIntSetting(Setting::N_U2F_KEYS_POST_AUTH),
            $appConfigManager->getIntSetting(Setting::N_U2F_KEYS_REG))
        ;
        $form = $this
            ->createForm(U2fConfigType::class, $submission)
            ->add('submit', SubmitType::class)
        ;
        $form->handleRequest($httpRequest);
        if ($form->isSubmitted() && $form->isValid()) {
            $appConfigManager->set(
                Setting::ALLOW_U2F_LOGIN,
                $submission->allowU2fLogin
            );
            $appConfigManager->set(
                Setting::N_U2F_KEYS_POST_AUTH,
                $submission->nU2fKeysPostAuth
            );
            $appConfigManager->set(
                Setting::N_U2F_KEYS_REG,
                $submission->nU2fKeysReg
            );
        }

        return $this->render('admin/registration_panel.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route(
     *  "/admin/security-strategy",
     *  name="admin_security_strategy")
     */
    public function processSecurityStrategyPage(
        AppConfigManager $config,
        Request $httpRequest)
    {
        $submission = new SecurityStrategySubmission(
            $config->getIntSetting(Setting::SECURITY_STRATEGY)
        );
        $form = $this
            ->createForm(SecurityStrategyType::class, $submission)
            ->add('submit', SubmitType::class)
        ;

        $form->handleRequest($httpRequest);
        if ($form->isSubmitted() && $form->isValid()) {
            $config->set(Setting::SECURITY_STRATEGY, $submission->securityStrategyId);
        }

        return $this->render('admin/security_strategy.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
