<?php

namespace App\Controller;

use App\Service\AuthRequestService;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LoginU2FController extends AbstractController
{
    private $auth;
    private $request;

    public function __construct(AuthRequestService $auth)
    {
        $this->auth = $auth;
        $this->request = Request::createFromGlobals();        
    }

    /**
     * @Route("u2f-login", name="display_u2f_login", methods={"POST"})
     */
    public function displayPage()
    {
        $auth_data = $this->auth->generate($this->request->get('username'));
        return $this->render('u2f-login.html.twig', $auth_data);
    }

    /**
     * @Route("process-u2f-login", name="process_u2f_login", methods={"POST"})
     */
    public function processU2FLogin()
    {
        $this->auth->processResponse(
            $this->request->get('auth-id'),
            $this->request->get('username'),
            $this->request->get('response')
        );
        return $this->render('successful-u2f-login.html.twig');
    }
}