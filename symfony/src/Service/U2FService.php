<?php

namespace App\Service;

use Firehed\U2F\Server;

class U2FService
{
    private $server;

    public function __construct()
    {
        $this->server = new Server();
        $this->server->disableCAVerification()
             ->setAppId('https://'.$_SERVER['SERVER_NAME']);
    }

    public function getServer()
    {
        return $this->server;
    }
}