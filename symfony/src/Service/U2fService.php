<?php

namespace App\Service;

use Firehed\U2F\Server;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class U2fService
{
    const N_U2F_TOKENS_PER_MEMBER = 3;

    private $appId;

    public function __construct(
        ContainerInterface $container,
        KernelInterface $kernel)
    {
        $this->appId = file($kernel->getProjectDir().'/app_id', FILE_IGNORE_NEW_LINES)[0];
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
