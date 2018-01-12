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
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function serialize(): string
    {
        return serialize([
            $this->username,
        ]);
    }

    public function unserialize($serialized): void
    {
        list(
            $this->username,
        ) = unserialize($serialized);
    }
}