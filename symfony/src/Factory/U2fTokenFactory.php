<?php

namespace App\Factory;

use App\Entity\U2fToken;

class U2fTokenFactory
{
    public function setCounter(
        U2fToken $u2fToken,
        int $counter): U2fToken
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
