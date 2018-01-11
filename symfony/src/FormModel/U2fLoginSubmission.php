<?php

namespace App\FormModel;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @todo Remove username.
 */
class U2fLoginSubmission
{
    private $username;

    private $u2fAuthenticationRequestId;

    private $u2fTokenResponse;

    public function __construct(
        ?string $username = null,
        ?string $u2fTokenResponse = null,
        ?string $u2fAuthenticationRequestId = null)
    {
        $this->username = $username;
        $this->u2fTokenResponse = $u2fTokenResponse;
        $this->u2fAuthenticationRequestId = $u2fAuthenticationRequestId;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getU2fAuthenticationRequestId(): ?string
    {
        return $this->u2fAuthenticationRequestId;
    }

    public function getU2fTokenResponse(): ?string
    {
        return $this->u2fTokenResponse;
    }

    public function setUsername(?string $username): void
    {
        $this->username = $username;
    }

    public function setU2fAuthenticationRequestId(?string $u2fAuthenticationRequestId): void
    {
        $this->u2fAuthenticationRequestId = $u2fAuthenticationRequestId;
    }

    public function setU2fTokenResponse(?string $u2fTokenResponse): void
    {
        $this->u2fTokenResponse = $u2fTokenResponse;
    }
}