<?php

namespace App\Service;

use Firehed\U2F\RegisterResponse;

/**
 * @todo interface for request ids?
 */
class RegisterRequestService
{
    private $pdo;
    private $server;
    private $session;

    public function __construct(U2FService $u2f, SecureSessionService $session, PDOService $pdo_service)
    {
        $this->server = $u2f->getServer();
        $this->session = $session;
        $this->pdo = $pdo_service->getPdo();
    }

    public function generate(): array
    {
        $request = $this->server->generateRegisterRequest();
        $request_id = $this->session->store(serialize($request));
        $request_json = json_encode($request);
        $registrations = \firehed\u2f\get_registrations_for_user(0, $this->pdo);
        $sign_requests = json_encode($this->server->generateSignRequests($registrations, $request_id));
        return array(
            'request_id' => $request_id,
            'request_json' => $request_json,
            'sign_requests' => $sign_requests,
        );
    }

    public function processResponse(string $request_id, string $username, string $challenge): void
    {
        $request = unserialize($this->session->getAndRemove($request_id));
        $this->server->setRegisterRequest($request);
        $response = RegisterResponse::fromJson($challenge);
        $registration = $this->server->register($response);
        $this->pdo->beginTransaction();
        $members_insert = $this->pdo->prepare('INSERT INTO members VALUES (NULL, :username)');
        $members_insert->bindParam(':username', $username);
        $success = $members_insert->execute();
        $u2f_authenticators_insert = $this->pdo->prepare('INSERT INTO u2f_authenticators VALUES (NULL, :member_id, :counter, :attestation, :public_key, :key_handle)');
        $member_id = $this->pdo->lastInsertId();
        $u2f_authenticators_insert->bindParam(':member_id', $member_id);
        $attestation = base64_encode($registration->getAttestationCertificateBinary());
        $u2f_authenticators_insert->bindParam(':attestation', $attestation);
        $counter = $registration->getCounter();
        $u2f_authenticators_insert->bindParam(':counter', $counter);
        $public_key = base64_encode($registration->getPublicKey());
        $u2f_authenticators_insert->bindParam(':public_key', $public_key);
        $key_handle = base64_encode($registration->getKeyHandleBinary());
        $u2f_authenticators_insert->bindParam(':key_handle', $key_handle);
        $u2f_authenticators_insert->execute();
        $this->pdo->commit();
    }
}