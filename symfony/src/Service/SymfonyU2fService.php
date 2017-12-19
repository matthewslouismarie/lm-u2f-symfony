<?php

namespace App\Service;

/**
 * adaptor class for firehed
 */
class SymfonyU2fService
{
    /**
     * @todo $server should be configurable
     */
    public function __construct()
    {
        $server = new Server();
        $server->disableCAVerification();
        $server->setAppId('https://shift-two.alwaysdata.net');
    }
}