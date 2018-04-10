<?php

namespace App\Controller;

use App\Form\ChallengeSpecificationType;
use App\Form\ConfigImportType;
use App\Form\PwdConfigType;
use App\Form\SecurityStrategyType;
use App\Enum\Setting;
use App\Form\U2fConfigType;
use App\Form\UserStudyConfigType;
use App\FormModel\ConfigImportSubmission;
use App\FormModel\PwdConfigSubmission;
use App\FormModel\SecurityStrategySubmission;
use App\FormModel\U2fConfigSubmission;
use App\FormModel\UserStudyConfigSubmission;
use App\Repository\PageMetricRepository;
use App\Service\AppConfigManager;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AdminDashboardController extends AbstractController
{
    const EXPORT_FILENAME = "options.json";

    /**
     * @Route(
     *  "/admin",
     *  name="admin")
     */
    public function getDashboard(AppConfigManager $config)
    {
        $participantId = $config->getStringSetting(Setting::PARTICIPANT_ID);
        
        return $this->render('admin/admin_overview.html.twig', [
            'participantId' => $participantId,
        ]);
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
            $config->getBoolSetting(Setting::ALLOW_PWD_LOGIN),
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
            $config->set(Setting::ALLOW_PWD_LOGIN, $submission->allowPwdAuthentication);
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
            $appConfigManager->getSetting(Setting::ALLOW_U2F_LOGIN, 'boolean'),
            $appConfigManager->getSetting(Setting::N_U2F_KEYS_POST_AUTH, 'integer'),
            $appConfigManager->getSetting(Setting::N_U2F_KEYS_REG, 'integer'),
            $appConfigManager->getSetting(Setting::ALLOW_MEMBER_TO_MANAGE_U2F_KEYS, 'boolean')
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
            $config->getSetting(Setting::SECURITY_STRATEGY, 'string')
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
     *  "/admin/metrics/{participantId}",
     *  name="admin_metrics")
     */
    public function metrics(
        string $participantId,
        AppConfigManager $config,
        PageMetricRepository $repository)
    {
        $metrics = $repository->getArray($participantId);
        $participantIds = $repository->getParticipantIdsExcept($participantId);

        return $this->render('admin/metrics.html.twig', [
            'metrics' => $metrics,
            'participantId' => $participantId,
            'participantIds' => $participantIds,
        ]);
    }

    /**
     * @Route(
     *  "/admin/export",
     *  name="admin_export")
     */
    public function exportToJson(AppConfigManager $config)
    {
        $response = new Response();

        //set headers
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment;filename="'.self::EXPORT_FILENAME.'"');

        $response->setContent($config->toJson());

        return $response;
    }

    /**
     * @Route(
     *  "/admin/import",
     *  name="admin_import")
     */
    public function importFromJson(AppConfigManager $config, Request $httpRequest)
    {
        $submission = new ConfigImportSubmission($config->toJson());
        $form = $this->createForm(ConfigImportType::class, $submission);
        $form->handleRequest($httpRequest);
        if ($form->isSubmitted() && $form->isValid()) {
            $config->fromJson($submission->jsonConfig);
        }
        return $this->render('admin/import.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route(
     *  "/admin/challenge-specification",
     *  name="admin_challenge_specification")
     */
    public function setChallengeSpecification(
        AppConfigManager $config,
        Request $httpRequest)
    {
        $form = $this->createForm(ChallengeSpecificationType::class);

        $form->handleRequest($httpRequest);
        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($form->all() as $field) {
                if ('submit' !== $field->getName()) {
                    $config->set($field->getName(), $field->getData());
                }
            }
        }

        return $this->render('admin/challenge_specification.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
