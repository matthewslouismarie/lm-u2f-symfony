<?php

namespace App\Service;

use App\Entity\Member;
use App\Entity\U2fToken;
use App\Model\U2fRegistrationRequest;
use Doctrine\ORM\EntityManagerInterface;
use Firehed\U2F\RegisterRequest;
use Firehed\U2F\RegisterResponse;

class U2fRegistrationManager
{
    private $server;

    private $session;

    private $em;

    public function __construct(EntityManagerInterface $em, U2fService $u2f,
                                SecureSession $session)
    {
        $this->server = $u2f->getServer();
        $this->session = $session;
        $this->em = $em;
    }

    /**
     * @todo $registrations
     * @todo Should return an object, e.g. RpRequest, or even IRPRequest.
     * @todo Make stateless.
     */
    public function generate(): U2fRegistrationRequest
    {
        $request = $this->server->generateRegisterRequest();
        $registrations = array();
        $signRequests = json_encode($this->server->generateSignRequests($registrations));

        return new U2fRegistrationRequest($request, $signRequests);
    }

    public function getU2fTokenFromResponse(
        string $u2fKeyResponse,
        Member $member,
        \DateTimeImmutable $registration_date_time,
        string $request_id): U2fToken
    {
        $request = $this->session->getAndRemoveObject($request_id, RegisterRequest::class);
        $this->server->setRegisterRequest($request);
        $response = RegisterResponse::fromJson($u2fKeyResponse);
        $registration = $this->server->register($response);

        $counter = $registration->getCounter();
        $attestation = base64_encode($registration->getAttestationCertificateBinary());
        $public_key = base64_encode($registration->getPublicKey());
        $key_handle = base64_encode($registration->getKeyHandleBinary());
        $u2fToken = new U2fToken(
            null,
            $attestation,
            $counter,
            $key_handle,
            $member,
            $registration_date_time,
            $public_key);

        return $u2fToken;
    }

    /**
     * @todo Change challenge for u2fKeyResponse.
     * @todo Shouldn't change the database directly.
     * @todo Make stateless.
     */
    public function processResponse(
        string $challenge,
        Member $member,
        \DateTimeImmutable $registration_date_time,
        string $request_id): U2fToken
    {
        $u2fToken = $this->getU2fTokenFromResponse(
            $challenge,
            $member,
            $registration_date_time,
            $request_id
        );
        $this->em->persist($u2fToken);

        $this->em->flush();

        return $u2fToken;
    }
}
