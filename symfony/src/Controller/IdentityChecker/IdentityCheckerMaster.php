<?php

namespace App\Controller\IdentityChecker;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class IdentityCheckerMaster extends AbstractController
{
    /**
     * @Route(
     *  "/all/initiate-identity-check/{sid}",
     *  name="ic_initialization")
     */
    public function initiateIdentityCheck()
    {
        return $this->render('identity_checker/initialization.html.twig');
    }
}
