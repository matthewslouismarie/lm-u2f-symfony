<?php

namespace App\Model;

use App\FormModel\U2fAuthenticationRequest;

class U2fAuthenticationCycle
{
    private $request;

    private $response;

    public function __construct(
        U2fAuthenticationRequest $request,
        string $response
    ) {
        $this->request = $request;
        $this->response = $response;
    }

    public function getRequest(): U2fAuthenticationRequest
    {
        return $this->request;
    }

    public function getResponse(): string
    {
        return $this->response;
    }
}
