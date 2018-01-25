<?php

namespace App\Model;

use Firehed\U2F\RegisterRequest;

class U2fRegistrationCycle
{
    private $request;

    private $stringResponse;

    public function __construct(
        RegisterRequest $request,
        string $stringResponse)
    {
        $this->request = $request;
        $this->stringResponse = $stringResponse;
    }

    public function getRequest(): RegisterRequest
    {
        return $this->request;
    }

    public function getResponse(): string
    {
        return $this->stringResponse;
    }
}
