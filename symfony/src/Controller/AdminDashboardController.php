<?php

namespace App\Controller;

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
        $submission = new RegistrationConfigSubmission();
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
}
