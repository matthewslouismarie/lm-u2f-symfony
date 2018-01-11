<?php

namespace App\TransitingUserInput;

use App\Model\IAuthorizationRequest;
use Serializable;

class UToU2fUserInput implements Serializable
{
    private $username;
    private $authorizationRequest;

    public function __construct(
        string $username,
        IAuthorizationRequest $authorizationRequest)
    {
        $this->username = $username;
        $this->authorizationRequest = $authorizationRequest;
    }

    public function getAuthorizationRequest(): IAuthorizationRequest
    {
        return $this->authorizationRequest;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function serialize(): string
    {
        return serialize([
            $this->username,
            $this->authorizationRequest,
        ]);
    }

    public function unserialize($serialized)
    {
        list(
            $this->username,
            $this->authorizationRequest,
        ) = unserialize($serialized);
    }
}