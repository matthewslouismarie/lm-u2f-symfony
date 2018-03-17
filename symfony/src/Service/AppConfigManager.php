<?php

namespace App\Service;

use App\Repository\AppSettingRepository;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AppConfigManager
{
    const CONFIG_FILENAME = 'app_config.json';
    const DEFAULT_CONFIG_FILENAME = 'default_app_config.json';

    const REG_N_U2F_KEYS = 0;
    const POST_AUTH_N_U2F_KEYS = 1;
    const ALLOW_U2F_LOGIN = 2;
    const SECURITY_STRATEGY = 3;

    private $defaultConfigArray;
    private $appConfigRepo;

    /**
     * @todo More specific exception.
     */
    public function __construct(
        AppSettingRepository $appConfigRepo,
        ContainerInterface $container)
    {
        $this->appConfigRepo = $appConfigRepo;
        $defaultConfigStr = file_get_contents(
            $container->getParameter('kernel.project_dir').'/'.self::DEFAULT_CONFIG_FILENAME)
        ;
        $defaultConfigArray = json_decode($defaultConfigStr, true);
        if (JSON_ERROR_NONE === json_last_error()) {
            $this->defaultConfigArray = $defaultConfigArray;
        } else {
            throw new Exception();
        }
    }

    public function getBoolSetting(int $id): bool
    {
        $valueStr = $this->appConfigRepo->get($id) ?? $this->defaultConfigArray[$id];
        return (bool) $valueStr;
    }

    public function getIntSetting(int $id): int
    {
        $valueStr = $this->appConfigRepo->get($id) ?? $this->defaultConfigArray[$id];
        return intval($valueStr);
    }

    public function getStringSetting(int $id): string
    {
        return $this->appConfigRepo->get($id) ?? $this->defaultConfigArray[$id];
    }

    /**
     * @todo Use a more specific exception.
     */
    public function set(int $id, string $value): void
    {
        $this->appConfigRepo->set($id, $value);
    }
}
