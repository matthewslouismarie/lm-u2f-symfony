<?php

namespace App\Security\Token;

use Serializable;

class AuthenticationToken implements Serializable
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

    public function serialize()
    {
        return $this->username;
    }

    public function unserialize($serialized)
    {
        $this->username = $serialized;
    }
}
