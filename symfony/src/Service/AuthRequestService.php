<?php

namespace App\Service;

use Firehed\U2F\SignResponse;

class AuthRequestService
{
    private $server;
    private $session;
    private $pdo;

    public function __construct(U2FService $u2f, PDOService $pdo, SecureSessionService $session)
    {
        $this->server = $u2f->getServer();
        $this->pdo = $pdo;
        $this->session = $session;
    }

    public function generate(string $username): array
    {
        $registrations = $this->pdo->getRegistrationsForUser($username, $this->pdo->getPdo());

        $sign_requests = $this->server->generateSignRequests($registrations);
        $auth_id = $this->session->store(serialize($sign_requests));

        return array(
            'sign_requests_json' => json_encode(array_values($sign_requests)),
            'username' => $username,
            'auth_id' => $auth_id
        );
    }

    /**
     * @todo sql transaction
     */
    public function processResponse(string $auth_id, string $username, string $token_response)
    {
        $sign_requests = unserialize($this->session->getAndRemove($auth_id));
        $this->server->setRegistrations($this->pdo->getRegistrationsForUser($username, $this->pdo->getPdo()))
               ->setSignRequests($sign_requests);
        $response = SignResponse::fromJson($token_response);
        $registration = $this->server->authenticate($response);

        $challenge = $response->getClientData()->getChallenge();
        $u2f_authenticator_id = $this->getAuthenticatorId($sign_requests, $challenge);
        
        // log in successful
        $stmt = $this->pdo->getPdo()->prepare('UPDATE u2f_authenticators SET counter = :counter WHERE id = :id;');
        $counter = $response->getCounter();
        $stmt->bindParam('counter', $counter);
        $stmt->bindParam('id', $u2f_authenticator_id);
        $stmt->execute();
    }

    private function getAuthenticatorId(array $sign_requests, string $challenge): string
    {
        foreach ($sign_requests as $authenticator_id => $sign_request) {
            if ($sign_request->getChallenge() === $challenge) {
                return $authenticator_id;
            }
        }
    }
}