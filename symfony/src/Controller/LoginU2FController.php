<?php

namespace App\Controller;

use App\Service\AuthRequestService;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class LoginU2FController extends Controller
{
    private $auth;

    public function __construct(AuthRequestService $auth)
    {
        $this->auth = $auth;
    }

    /**
     * @Route("u2f-login", name="display_u2f_login", methods={"POST"})
     */
    public function displayPage(Request $request)
    {
        $auth_data = $this->auth->generate($request->get('username'));
        return $this->render('u2f-login.html.twig', $auth_data);
    }

    /**
     * @Route("process-u2f-login", name="process_u2f_login", methods={"POST"})
     */
    public function processU2FLogin(Request $request)
    {
        $this->auth->processResponse(
            $request->get('auth-id'),
            $request->get('username'),
            $request->get('response')
        );
        return $this->render('successful_u2f_login.html.twig');
    }
}