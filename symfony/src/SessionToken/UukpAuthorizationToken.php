<?php

namespace App\SessionToken;

use Serializable;

class UukpAuthorizationToken implements Serializable
{
    private $username;
    private $firstU2fTokenUsed;
    private $secondU2fTokenUsed;
    
    public function __construct(
        string $username,
        int $firstU2fTokenUsed,
        int $secondU2fTokenUsed)
    {
        $this->username = $username;
        $this->firstU2fTokenUsed = $firstU2fTokenUsed;
        $this->secondU2fTokenUsed = $secondU2fTokenUsed;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getFirstU2fTokenUsed(): int
    {
        return $this->firstU2fTokenUsed;
    }

    public function getSecondU2fTokenUsed(): int
    {
        return $this->secondU2fTokenUsed;
    }

    public function serialize(): string
    {
        return serialize([
            $this->username,
            $this->firstU2fTokenUsed,
            $this->secondU2fTokenUsed,
        ]);
    }

    public function unserialize($serialized): void
    {
        list(
            $this->username,
            $this->firstU2fTokenUsed,
            $this->secondU2fTokenUsed,
        ) = unserialize($serialized);
    }
}