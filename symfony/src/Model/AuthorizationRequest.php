<?php

namespace App\Model;

class AuthorizationRequest implements IAuthorizationRequest
{
    private $isAccepted;
    private $successRoute;

    public function __construct(
        bool $isAccepted,
        string $successRoute)
    {
        $this->isAccepted = $isAccepted;
        $this->successRoute = $successRoute;
    }

    public function isAccepted(): bool
    {
        return $this->isAuthorised;
    }

    public function getSuccessRoute(): string
    {
        return $this->successRoute;
    }
}