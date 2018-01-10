<?php

namespace App\Model;

interface IAuthorizationRequest
{
    public function isAccepted(): bool;
    public function getSuccessRoute(): string;
}