<?php

namespace App\FormModel;

class LoginRequest
{
    private $username;

    public function __construct(?string $username = null)
    {
        $this->username = $username;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(
        ?string $username): void
    {
        $this->username = $username;
    }
}
