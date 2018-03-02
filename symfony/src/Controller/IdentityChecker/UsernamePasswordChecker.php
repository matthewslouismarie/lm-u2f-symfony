<?php

namespace App\Controller\IdentityChecker;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class UsernamePasswordChecker extends AbstractController
{
    /**
     * @Route(
     *  "/all/check-username-and-password/{sid}",
     *  name="ic_usernamepassword")
     */
    public function checkUsernameAndPassword()
    {
        return $this->render('identity_checker/username_and_password.html.twig');
    }
}
