<?php

namespace App\Service;

use Firehed\U2F\Server;
use Symfony\Component\DependencyInjection\ContainerInterface;

class U2FService
{
    private $server;

    public function __construct(ContainerInterface $container)
    {
        $this->server = new Server();
        $this->server->disableCAVerification()
             ->setAppId($container->getParameter('u2f.app_id'));
    }

    public function getServer()
    {
        return $this->server;
    }
}