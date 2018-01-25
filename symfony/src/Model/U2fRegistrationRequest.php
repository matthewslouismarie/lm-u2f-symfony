<?php

namespace App\Model;

use Firehed\U2F\RegisterRequest;

class U2fRegistrationRequest
{
    private $request;

    private $signRequests;

    public function __construct(RegisterRequest $request, string $signRequests)
    {
        $this->request = $request;
        $this->signRequests = $signRequests;
    }

    public function getRequest(): RegisterRequest
    {
        return $this->request;
    }

    public function getRequestAsJson(): string
    {
        return json_encode($this->request);
    }

    public function getSignRequests(): string
    {
        return $this->signRequests;
    }
}
