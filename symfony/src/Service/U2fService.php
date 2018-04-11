<?php

namespace App\Service;

use Firehed\U2F\Server;

class U2fService
{
    const N_U2F_TOKENS_PER_MEMBER = 3;

    private $appId;

    public function __construct(AppIdReader $appIdReader)
    {
        $this->appId = $appIdReader->getAppId();
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
