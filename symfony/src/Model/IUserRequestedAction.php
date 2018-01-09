<?php

namespace App\Model;

interface IUserRequestedAction
{
    public function isAuthorized(): bool;
    public function getSuccessUrl(): string;
}