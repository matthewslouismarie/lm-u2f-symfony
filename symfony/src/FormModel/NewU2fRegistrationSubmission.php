<?php

namespace App\FormModel;

use Serializable;

class NewU2fRegistrationSubmission implements Serializable
{
    private $u2fTokenResponse;

    public function __construct(?string $u2fTokenResponse = null)
    {
        $this->u2fTokenResponse = $u2fTokenResponse;
    }

    public function getU2fTokenResponse(): ?string
    {
        return $this->u2fTokenResponse;
    }

    public function setU2fTokenResponse(?string $u2fTokenResponse): void
    {
        $this->u2fTokenResponse = $u2fTokenResponse;
    }

    public function serialize(): string
    {
        return serialize([
            $this->u2fTokenResponse,
        ]);
    }

    public function unserialize($serialized): void
    {
        list(
            $this->u2fTokenResponse) = unserialize($serialized);
    }
}
