<?php

namespace App\Service;

class AuthRequestService
{
    private $server;
    private $session;
    private $pdo;

    public function __construct(U2FService $u2f, PDOService $pdo_service, SecureSessionService $session)
    {
        $this->server = $u2f->getServer();
        $this->pdo = $pdo_service->getPdo();
        $this->session = $session;
    }

    public function generate(string $username): array
    {
        $registrations = \firehed\u2f\get_registrations_for_user($username, $this->pdo);
        $sign_requests = $this->server->generateSignRequests($registrations);
        $auth_id = $this->session->store(serialize($sign_requests));

        return array(
            'sign_requests_json' => json_encode($sign_requests),
            'username' => $username,
            'auth_id' => $auth_id
        );
    }
}