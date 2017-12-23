<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LoginController extends AbstractController
{
    /**
     * @Route("login", name="login", methods={"GET"})
     */
    public function getLoginFirstPage()
    {
        return $this->render('login.html.twig');
    }
}