<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class TksUpukAuthenticationController extends AbstractController
{
    /**
     * @Route(
     *  "/tks-upuk/not-authenticated/authenticate",
     *  name="tks_authenticate",
     *  methods={"GET"})
     */
    public function authenticate()
    {
        return $this->render('tks/upuk/upuk_login.html.twig');
    }
}