<?php

namespace App\Service;

use Firehed\U2F\Server;
use Symfony\Component\Routing\RequestContext;

class U2FService
{
    private $server;

    public function __construct(RequestContext $context)
    {
        $this->server = new Server();
        $this->server->disableCAVerification()
             ->setAppId($context->getBaseUrl());
    }

    public function getServer()
    {
        return $this->server;
    }
}