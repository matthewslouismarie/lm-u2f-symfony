<?php

namespace App\Service;

use App\Service\U2FTokenBuilderService;
use App\Entity\Member;
use App\Entity\U2FToken;
use Doctrine\Common\Persistence\ObjectManager;
use Firehed\U2F\SignResponse;

/**
 * @todo Rename to U2FAuthService
 */
class AuthRequestService
{
    private $builder;
    private $em;
    private $server;
    private $session;

    public function __construct(
        ObjectManager $em,
        U2FService $u2f,
        U2FTokenBuilderService $builder,
        SecureSessionService $session)
    {
        $this->builder = $builder;
        $this->em = $em;
        $this->server = $u2f->getServer();
        $this->session = $session;
    }

    /**
     * @todo Rename auth_id to u2fAuthenticationId?
     */
    public function generate(
        string $username,
        array $idsToExclude = array()): array
    {
        $member = $this->em
        ->getRepository(Member::class)
        ->findOneBy(array('username' => $username));

        $registrations = $this
            ->em
            ->getRepository(U2FToken::class)
            ->getMemberRegistrations($member->getId())
        ;
        
        $unfilteredSignRequests = $this
            ->server
            ->generateSignRequests($registrations)
        ;
        $signRequests = array();
        foreach ($unfilteredSignRequests as $key => $signRequest) {
            if (!in_array($key, $idsToExclude)) {
                $signRequests[$key] = $signRequest;
            }
        }

        $auth_id = $this->session->storeArray($signRequests);

        return array(
            'sign_requests_json' => json_encode(array_values($signRequests)),
            'username' => $username,
            'auth_id' => $auth_id,
            'tmp' => $signRequests,
        );
    }

    /**
     * @todo Critical vulnerability! The user is able to modify the U2F
     * authentication ID!
     * @todo Make stateless.
     * @todo sql transaction
     */
    public function processResponse(
        string $auth_id,
        string $username,
        string $token_response): int
    {
        $member = $this->em
                       ->getRepository(Member::class)
                       ->findOneBy(array('username' => $username));

        $registrations = $this->em
                              ->getRepository(U2FToken::class)
                              ->getMemberRegistrations($member->getId());

        $sign_requests = $this->session->getAndRemoveArray($auth_id);
        $this->server
             ->setRegistrations($registrations)
             ->setSignRequests($sign_requests);
        $response = SignResponse::fromJson($token_response);
        $registration = $this->server->authenticate($response);

        $challenge = $response->getClientData()->getChallenge();
        $u2f_authenticator_id = $this->getAuthenticatorId($sign_requests, $challenge);

        $u2fToken = $this->em
            ->getRepository(U2FToken::class)
            ->find($u2f_authenticator_id)
        ;
        $u2fToken->setCounter($response->getCounter());
        $this->em->flush();

        return $u2f_authenticator_id;
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