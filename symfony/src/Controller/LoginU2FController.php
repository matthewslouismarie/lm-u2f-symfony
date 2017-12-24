<?php

namespace App\Controller;

use App\Service\PDOService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Firehed\U2F\Server;

class LoginU2FController extends AbstractController
{
    private $session;

    public function __construct()
    {
        $this->session = new Session();
    }

    /**
     * @Route("u2f-login", name="u2f_login", methods={"POST"})
     */
    public function doPost(PDOService $pdo)
    {
        $request = Request::createFromGlobals();
        $server = new Server();
        $server->disableCAVerification()
               ->setAppId('https://'.$_SERVER['SERVER_NAME']);

        $auth_id = \firehed\u2f\generate_auth_id();
        $registrations = \firehed\u2f\get_registrations_for_user($_POST['username'], $pdo->getPdo());
        $sign_requests = $server->generateSignRequests($registrations, $auth_id);

        $old = $this->session->get($auth_id);
        $old['sign_requests'] = serialize($sign_requests);
        $this->session->set($auth_id, $old);

        return $this->render('u2f-login.html.twig', array(
            'sign_requests_json' => json_encode($sign_requests),
            'username' => $_POST['username'],
            'auth_id' => $auth_id
        ));
    }
}