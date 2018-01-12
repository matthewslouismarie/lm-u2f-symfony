<?php

namespace App\Model;

use Serializable;

interface IAuthorizationRequest extends Serializable
{
    public function isAccepted(): bool;

    public function getSuccessRoute(): string;

    public function getUsername(): ?string;
}
