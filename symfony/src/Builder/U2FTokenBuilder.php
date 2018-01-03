<?php

namespace App\Builder;

use App\Entity\Member;
use App\Entity\U2FToken;

class U2FTokenBuilder
{
    private $base;

    public function __construct(U2FToken $u2fToken)
    {
        $this->base = $u2fToken;
    }

    public function setCounter(int $counter): U2FToken
    {
        return new U2FToken(
            $this->base->getAttestation(),
            $counter,
            $this->base->getKeyHandle(),
            $this->base->getMember(),
            $this->base->getName(),
            $this->base->getRegistrationDateTime,
            $this->base->getPublicKey());
    }
}