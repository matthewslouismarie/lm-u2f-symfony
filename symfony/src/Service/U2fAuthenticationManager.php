<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\U2f\NoRegisteredU2fTokenException;
use App\Entity\Member;
use App\Entity\U2fToken;
use App\FormModel\U2fAuthenticationRequest;
use Doctrine\Common\Persistence\ObjectManager;
use Firehed\U2F\SignResponse;

/**
 * @todo Delete.
 */
class U2fAuthenticationManager
{
    private $em;

    private $u2fService;

    private $session;

    public function __construct(
        ObjectManager $em,
        U2fService $u2fService,
        SecureSession $session
    ) {
        $this->em = $em;
        $this->u2fService = $u2fService;
        $this->session = $session;
    }

    public function generate(
        string $username,
        array $idsToExclude = []
    ): U2fAuthenticationRequest {
        $member = $this
            ->em
            ->getRepository(Member::class)
            ->findOneBy(['username' => $username])
        ;

        $registrations = $this
            ->em
            ->getRepository(U2fToken::class)
            ->getMemberRegistrations($member)
        ;

        $signRequests = $this
            ->u2fService
            ->getServer()
            ->generateSignRequests($registrations)
        ;

        foreach ($idsToExclude as $id) {
            unset($signRequests[$id]);
        }

        if (0 === count($signRequests)) {
            throw new NoRegisteredU2fTokenException();
        }

        $u2fAuthenticationProcess = new U2fAuthenticationRequest($signRequests);

        return $u2fAuthenticationProcess;
    }

    public function processResponse(
        U2fAuthenticationRequest $u2fAuthenticationProcess,
        string $username,
        string $u2fTokenResponse
    ): int {
        $server = $this
            ->u2fService
            ->getServer()
        ;
        $member = $this
            ->em
            ->getRepository(Member::class)
            ->findOneBy(['username' => $username])
        ;

        $registrations = $this
            ->em
            ->getRepository(U2fToken::class)
            ->getMemberRegistrations($member)
        ;

        $server
            ->setRegistrations($registrations)
            ->setSignRequests($u2fAuthenticationProcess->getSignRequests())
        ;
        $response = SignResponse::fromJson($u2fTokenResponse);
        $registration = $server->authenticate($response);

        $challenge = $response->getClientData()->getChallenge();
        $u2fAuthenticatorId = $this->getAuthenticatorId($u2fAuthenticationProcess->getSignRequests(), $challenge);

        $u2fToken = $this
            ->em
            ->getRepository(U2fToken::class)
            ->find($u2fAuthenticatorId)
        ;
        $u2fToken->setCounter($response->getCounter());
        $this->em->flush();

        return $u2fAuthenticatorId;
    }

    private function getAuthenticatorId(
        array $sign_requests,
        string $challenge
    ): string {
        foreach ($sign_requests as $authenticator_id => $sign_request) {
            if ($sign_request->getChallenge() === $challenge) {
                return $authenticator_id;
            }
        }
    }
}
