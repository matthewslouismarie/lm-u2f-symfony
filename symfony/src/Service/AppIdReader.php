<?php

namespace App\Service;

use Symfony\Component\HttpKernel\KernelInterface;

class AppIdReader
{
    private $appId;

    private $appIdDist;

    public function __construct(KernelInterface $kernel)
    {
        $this->appId = $kernel->getProjectDir().'/app_id';
        $this->appIdDist = $kernel->getProjectDir().'/app_id.dist';
    }

    public function getAppId(): string
    {
        if (is_readable($this->appId)) {
            $appIdContent = file($this->appId, FILE_IGNORE_NEW_LINES);

            return $appIdContent[0];
        } else {
            $appIdDistContent = file($this->appIdDist, FILE_IGNORE_NEW_LINES);

            return $appIdDistContent[0];
        }
    }
}
