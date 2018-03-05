<?php

namespace App\Controller;

// use Symfony\Component\HttpFoundation\
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
        return $this->render('admin.html.twig');
    }
}
