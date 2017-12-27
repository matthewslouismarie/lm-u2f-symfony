<?php

namespace App\Controller;

use App\Service\AuthRequestService;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LoginU2FController extends AbstractController
{
    /**
     * @Route("u2f-login", name="u2f_login", methods={"POST"})
     */
    public function displayPage(AuthRequestService $auth)
    {
        $request = Request::createFromGlobals();
        $auth_data = $auth->generate($request->get('username'));
        return $this->render('u2f-login.html.twig', $auth_data);
    }
}