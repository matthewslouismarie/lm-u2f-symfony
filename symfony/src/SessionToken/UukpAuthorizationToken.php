<?php

namespace App\SessionToken;

use Serializable;

class UukpAuthorizationToken implements Serializable
{
    private $username;
    
    public function __construct(string $username)
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