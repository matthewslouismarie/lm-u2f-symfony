<?php

namespace App\Service;

use Symfony\Component\HttpKernel\KernelInterface;

class AppIdReader
{
    private $projectDir;

    public function __construct(KernelInterface $kernel)
    {
        $this->projectDir = $kernel->getProjectDir();
    }

    public function getAppId(): string
    {
        $appId = file($this->projectDir.'/app_id', FILE_IGNORE_NEW_LINES);
        if (false !== $appId) {
            return $appId[0];
        } else {
            $appIdDist = file($this->projectDir.'/app_id.dist', FILE_IGNORE_NEW_LINES);
            return $appIdDist[0];
        }
    }
}
