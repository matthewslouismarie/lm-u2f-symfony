<?php

namespace App\Service\Authentifier;

use LM\Authentifier\Configuration\IApplicationConfiguration;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Symfony\Component\Asset\Packages;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig_Environment;
use Twig_Function;

class Configuration implements IApplicationConfiguration
{
    private $assetPackage;

    private $appId;

    private $container;

    public function __construct(
        Packages $assetPackage,
        Twig_Environment $twig,
        ContainerInterface $container)
    {
        $this->appId = $container->getParameter("u2f.app_id");
        $this->assetPackage = $assetPackage;
        $this->container = $container;
    }

    public function getAssetUri(string $assetId): string
    {
        return $this->assetPackage->getUrl($assetId);
    }

    public function getAppId(): string
    {
        return $this->appId;
    }

    public function getContainer(): PsrContainerInterface
    {
        return $this->container;
    }

    public function save(): void
    {
    }
}
