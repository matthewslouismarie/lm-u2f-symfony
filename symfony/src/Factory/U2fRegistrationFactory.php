<?php

namespace App\Factory;

use App\Entity\U2fToken;
use DateTimeImmutable;
use LM\Authentifier\Model\IU2fRegistration;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class U2fRegistrationFactory
{
    private $member;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->member = $tokenStorage
            ->getToken()
            ->getUser()
        ;
    }

    public function toEntity(IU2fRegistration $registration): U2fToken
    {
        return new U2fToken(
            null,
            base64_encode($registration->getAttestationCertificateBinary()),
            $registration->getCounter(),
            base64_encode($registration->getKeyHandleBinary()),
            $this->member,
            new DateTimeImmutable(),
            base64_encode($registration->getPublicKeyBinary()),
            'Key '.microtime()
        );
    }
}
