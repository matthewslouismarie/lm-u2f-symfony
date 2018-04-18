<?php

namespace App\Factory;

use App\Entity\Member;
use App\Entity\U2fToken;
use DateTimeImmutable;
use LM\Authentifier\Model\IU2fRegistration;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class U2fRegistrationFactory
{
    public function toEntity(IU2fRegistration $registration, Member $member): U2fToken
    {
        return new U2fToken(
            null,
            $registration->getAttestationCertificate(),
            $registration->getCounter(),
            $registration->getKeyHandle(),
            $member,
            new DateTimeImmutable(),
            $registration->getPublicKey(),
            'Key '.microtime()
        );
    }
}
