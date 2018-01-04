<?php

namespace App\Controller\MasterKeyPairStrategy;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/mkps/registration", name="mkps_registration", methods={"GET"})
     */
    public function fetchRegistrationPage()
    {
        return $this->render('mkps/registration.html.twig');
    }
}