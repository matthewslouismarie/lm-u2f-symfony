<?php

namespace App\Service;

use Firehed\U2F\Server;
use Symfony\Component\DependencyInjection\ContainerInterface;

class U2fService
{
    const N_U2F_TOKENS_PER_MEMBER = 3;

    private $appId;

    public function __construct(ContainerInterface $container)
    {
        $this->appId = $container->getParameter('u2f.app_id');
    }

    public function getServer(): Server
    {
        $server = new Server();
        $server
            ->disableCAVerification()
            ->setAppId($this->appId)
        ;

        return $server;
    }
}
