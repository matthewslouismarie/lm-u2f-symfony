<?php

namespace App\Builder;

use App\Entity\U2fToken;

class U2fTokenBuilder
{
    private $base;

    public function __construct(U2fToken $u2fToken)
    {
        $this->base = $u2fToken;
    }

    public function setCounter(int $counter): U2fToken
    {
        return new U2fToken(
            $this->base->getId(),
            $this->base->getAttestation(),
            $counter,
            $this->base->getKeyHandle(),
            $this->base->getMember(),
            $this->base->getRegistrationDateTime(),
            $this->base->getPublicKey());
    }
}
