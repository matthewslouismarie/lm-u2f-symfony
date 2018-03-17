<?php

namespace App\Controller;

use App\Form\SecurityStrategyType;
use App\FormModel\SecurityStrategySubmission;
use App\Form\RegistrationConfigType;
use App\FormModel\RegistrationConfigSubmission;
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
        $submission = new RegistrationConfigSubmission(
            $appConfigManager->getIntSetting(AppConfigManager::REG_N_U2F_KEYS))
        ;
        $form = $this
            ->createForm(RegistrationConfigType::class, $submission)
            ->add('submit', SubmitType::class)
        ;
        $form->handleRequest($httpRequest);
        if ($form->isSubmitted() && $form->isValid()) {
            $appConfigManager->set(
                AppConfigManager::REG_N_U2F_KEYS,
                $submission->getNU2fKeys());
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
            $config->getIntSetting(AppConfigManager::SECURITY_STRATEGY)
        );
        $form = $this
            ->createForm(SecurityStrategyType::class, $submission)
            ->add('submit', SubmitType::class)
        ;

        $form->handleRequest($httpRequest);
        if ($form->isSubmitted() && $form->isValid()) {
            $config->set(AppConfigManager::SECURITY_STRATEGY, $submission->securityStrategyId);
        }

        return $this->render('admin/security_strategy.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
