<?php

declare(strict_types=1);

namespace App\Model;

use Serializable;

interface IAuthorizationRequest extends Serializable
{
    public function isAccepted(): bool;

    public function getSuccessRoute(): string;

    public function getUsername(): ?string;
}
