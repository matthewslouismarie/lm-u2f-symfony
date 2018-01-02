<?php

namespace App\Service;

use App\Entity\Member;
use App\Entity\U2FToken;
use Doctrine\ORM\EntityManagerInterface;
use Firehed\U2F\RegisterResponse;

/**
 * @todo interface for request ids?
 */
class AddU2FTokenService
{
    private $server;
    private $session;
    private $em;
    
    public function __construct(EntityManagerInterface $em, U2FService $u2f,
                                SecureSessionService $session)
    {
        $this->server = $u2f->getServer();
        $this->session = $session;
        $this->em = $em;
    }

    /**
     * @todo $registrations
     */
    public function generate(): array
    {
        $request = $this->server->generateRegisterRequest();
        $request_id = $this->session->store(serialize($request));
        $request_json = json_encode($request);
        $registrations = array();
        $sign_requests = json_encode($this->server->generateSignRequests($registrations, $request_id));
        return array(
            'request_id' => $request_id,
            'request_json' => $request_json,
            'sign_requests' => $sign_requests,
        );
    }

    public function processResponse(
        string $challenge,
        string $name,
        Member $member,
        \DateTimeImmutable $registration_date_time,
        string $request_id): void
    {
        $request = unserialize($this->session->getAndRemove($request_id));
        $this->server->setRegisterRequest($request);
        $response = RegisterResponse::fromJson($challenge);
        $registration = $this->server->register($response);

        $counter = $registration->getCounter();
        $attestation = base64_encode($registration->getAttestationCertificateBinary());
        $public_key = base64_encode($registration->getPublicKey());
        $key_handle = base64_encode($registration->getKeyHandleBinary());
        $u2f_token = new U2FToken(
            null,
            $attestation,
            $counter,
            $key_handle,
            $member,
            $name,
            $registration_date_time,
            $public_key);
        $this->em->persist($u2f_token);

        $this->em->flush();
    }
}