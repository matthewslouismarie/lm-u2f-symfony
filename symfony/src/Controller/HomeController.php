<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route(
     *  "/",
     *  name="homepage")
     */
    public function home(Request $request)
    {
        return $this->render('homepage.html.twig');
    }
}
