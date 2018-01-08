<?php

namespace App\Controller\Clearance0;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class PasswordUpdateController extends AbstractController
{
    /**
     * @Route(
     *  "/tks-0/authenticated/change-password",
     *  name="tks_change_password",
     *  methods={"GET"})
     */
    public function changePassword()
    {
        return new Response('all good');
    }
}