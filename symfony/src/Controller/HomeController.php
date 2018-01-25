<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route(
     *  "/all/",
     *  name="homepage")
     */
    public function home(Request $request)
    {
        ob_start();
        $content = ob_get_clean();

        return $this->render('home.html.twig', array('c' => $content));
    }

    /**
     * @Route(
     *  "/not-authenticated/login/{authorizationRequestSid}",
     *  name="login_success_route",
     *  methods={"GET"},
     *  requirements={"authorizationRequestSid"=".+"})
     */
    public function finishLogin(Request $request)
    {
        ob_start();
        $content = ob_get_clean();

        return $this->render('home.html.twig', array('c' => $content));
    }
}
