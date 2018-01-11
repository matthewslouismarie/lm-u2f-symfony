<?php

namespace App\Model;

class AuthorizationRequest implements IAuthorizationRequest, \Serializable
{
    private $isAccepted;
    private $successRoute;
    private $username;

    public function __construct(
        bool $isAccepted,
        string $successRoute,
        ?string $username)
    {
        $this->isAccepted = $isAccepted;
        $this->successRoute = $successRoute;
        $this->username = $username;
    }

    public function isAccepted(): bool
    {
        return $this->isAccepted;
    }

    public function getSuccessRoute(): string
    {
        return $this->successRoute;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function serialize(): string
    {
        return serialize([
            $this->isAccepted,
            $this->successRoute,
            $this->username,
        ]);
    }

    public function unserialize($serialize): void
    {
        list(
            $this->isAccepted,
            $this->successRoute,
            $this->username,
        ) = unserialize($serialize);
    }
}