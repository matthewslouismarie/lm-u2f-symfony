<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LoginController extends AbstractController
{
    /**
     * @Route("/username-login", name="username_login", methods={"GET"})
     */
    public function getLoginFirstPage()
    {
        return $this->render('username-login.html.twig');
    }
}