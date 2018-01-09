<?php

namespace App\Model;

class UserRequestedAction implements IUserRequestedAction
{
    public function __construct(
        bool $isAuthorized,
        bool $successUrl)
    {
        $this->isAuthorized = $isAuthorized;
        $this->successUrl = $successUrl;
    }

    public function isAuthorized(): bool
    {
        return $this->isAuthorised;
    }

    public function getSuccessUrl(): string
    {
        return $this->successUrl;
    }
}