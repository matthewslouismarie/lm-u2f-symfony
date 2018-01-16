<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HomeController extends Controller
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
