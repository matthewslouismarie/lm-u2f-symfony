<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Member;
use App\Entity\U2fToken;
use App\Model\U2fRegistrationRequest;
use DateTimeImmutable;
use Firehed\U2F\RegisterRequest;
use Firehed\U2F\RegisterResponse;

/**
 * @todo Delete.
 */
class U2fRegistrationManager
{
    private $u2f;

    public function __construct(U2fService $u2f)
    {
        $this->u2f = $u2f;
    }

    public function generate($registrations = []): U2fRegistrationRequest
    {
        $server = $this
            ->u2f
            ->getServer()
        ;
        $request = $server->generateRegisterRequest();
        $signRequests = json_encode($server->generateSignRequests($registrations));

        return new U2fRegistrationRequest($request, $signRequests);
    }

    public function getU2fTokenFromResponse(
        string $u2fKeyResponse,
        Member $member,
        DateTimeImmutable $registration_date_time,
        RegisterRequest $request,
        string $u2fKeyName
    ): U2fToken {
        $server = $this
            ->u2f
            ->getServer()
        ;
        $server
            ->setRegisterRequest($request)
        ;
        $response = RegisterResponse::fromJson($u2fKeyResponse);
        $registration = $server->register($response);

        $counter = $registration->getCounter();
        $attestation = base64_encode(
            $registration->getAttestationCertificateBinary()
        );
        $public_key = base64_encode($registration->getPublicKey());
        $key_handle = base64_encode($registration->getKeyHandleBinary());
        $u2fToken = new U2fToken(
            null,
            $attestation,
            $counter,
            $key_handle,
            $member,
            $registration_date_time,
            $public_key,
            $u2fKeyName
        );

        return $u2fToken;
    }
}
