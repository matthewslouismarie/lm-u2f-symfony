<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Firehed\U2F\Server;
use LM\Database\DatabaseConnection;
use App\Service\PDOService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RegisterController extends AbstractController
{
    private $pdo;

    public function __construct(PDOService $pdo)
    {
        $this->pdo = $pdo;
    }

    public function doGet(): Response
    {
        $server = new Server();
        $server->disableCAVerification()
       ->setAppId('https://shift-two.alwaysdata.net');

        $request = $server->generateRegisterRequest();
        $reg_id = \Firehed\U2F\generate_reg_id();
        $_SESSION[$reg_id]['register_request'] = serialize($request);
        $request_json = json_encode($request); // ->jsonSerialize()?

        $registrations = \firehed\u2f\get_registrations_for_user(0, $this->pdo->getPdo()); // @todo why do we need sign_requests for registration?
        $sign_requests = json_encode($server->generateSignRequests($registrations, $reg_id));
        return $this->render('template.twig');
    }
}