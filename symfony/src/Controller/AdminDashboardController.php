<?php

namespace App\Controller;

use App\Entity\PageMetric;
use App\Enum\Setting;
use App\Form\PwdConfigType;
use App\Form\SecurityStrategyType;
use App\Form\U2fConfigType;
use App\Form\UserStudyConfigType;
use App\FormModel\PwdConfigSubmission;
use App\FormModel\SecurityStrategySubmission;
use App\FormModel\U2fConfigSubmission;
use App\FormModel\UserStudyConfigSubmission;
use App\Repository\PageMetricRepository;
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
     *  "/admin/password",
     *  name="admin_password")
     */
    public function getPasswordPanel(
        AppConfigManager $config,
        Request $httpRequest)
    {
        $submission = new PwdConfigSubmission(
            $config->getIntSetting(Setting::PWD_MIN_LENGTH),
            $config->getBoolSetting(Setting::PWD_ENFORCE_MIN_LENGTH),
            $config->getBoolSetting(Setting::PWD_NUMBERS),
            $config->getBoolSetting(Setting::PWD_SPECIAL_CHARS),
            $config->getBoolSetting(Setting::PWD_UPPERCASE))
        ;
        $form = $this
            ->createForm(PwdConfigType::class, $submission)
            ->add('submit', SubmitType::class)
            ->handleRequest($httpRequest)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $config->set(Setting::PWD_MIN_LENGTH, $submission->minimumLength);
            $config->set(Setting::PWD_ENFORCE_MIN_LENGTH, $submission->enforceMinimumLength);
            $config->set(Setting::PWD_NUMBERS, $submission->requireNumbers);
            $config->set(Setting::PWD_SPECIAL_CHARS, $submission->requireSpecialCharacters);
            $config->set(Setting::PWD_UPPERCASE, $submission->requireUppercaseLetters);
        }

        return $this->render('admin/password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @todo Change name for U2F.
     *
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
            $appConfigManager->getIntSetting(Setting::N_U2F_KEYS_REG),
            $appConfigManager->getBoolSetting(Setting::ALLOW_MEMBER_TO_MANAGE_U2F_KEYS)
        );
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
            $appConfigManager->set(
                Setting::ALLOW_MEMBER_TO_MANAGE_U2F_KEYS,
                $submission->allowMemberToManageU2fKeys
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

    /**
     * @Route(
     *  "/admin/user-study",
     *  name="admin_user_study")
     */
    public function configureUserStudy(
        AppConfigManager $config,
        Request $httpRequest)
    {
        $submission = new UserStudyConfigSubmission(
            $config->getBoolSetting(Setting::USER_STUDY_MODE_ACTIVE),
            $config->getStringSetting(Setting::PARTICIPANT_ID)
        );
        $form = $this->createForm(UserStudyConfigType::class, $submission);

        $form->handleRequest($httpRequest);
        if ($form->isSubmitted() && $form->isValid()) {
            $config->set(
                Setting::USER_STUDY_MODE_ACTIVE,
                $submission->isUserStudyModeActive)
            ;
            if (true !== empty($submission->participantId)) {
                $config->set(
                    Setting::PARTICIPANT_ID,
                    $submission->participantId)
                ;
            }
        }

        return $this->render('admin/user_study.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route(
     *  "/admin/metrics",
     *  name="admin_metrics")
     */
    public function metrics(
        AppConfigManager $config,
        PageMetricRepository $repository)
    {
        $participantId = $config->getStringSetting(Setting::PARTICIPANT_ID);
        $metrics = $repository->getArray($participantId);
        // $metrics = $repository->findAll();

        return $this->render('admin/metrics.html.twig', [
            'metrics' => $metrics,
            'participantId' => $participantId,
        ]);
    }
}
