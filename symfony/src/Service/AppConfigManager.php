<?php

namespace App\Service;

use App\Enum\Setting;
use App\Repository\AppSettingRepository;
use Exception;
use LM\Common\Type\TypeCheckerTrait;
use Serializable;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @todo Could use a DataManager structure.
 * @todo Rename to GlobalConfig.
 */
class AppConfigManager
{
    use TypeCheckerTrait;

    const CONFIG_FILENAME = 'app_config.json';
    const DEFAULT_CONFIG_FILENAME = 'default_app_config.json';

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

    /**
     * @todo Remove.
     */
    public function get(string $id)
    {
        return $this->appConfigRepo->get($id) ?? $this->defaultConfigArray[$id];
    }

    /**
     * @todo Remove.
     */
    public function getBoolSetting($id): bool
    {
        $valueStr = $this->appConfigRepo->get($id) ?? $this->defaultConfigArray[$id];
        return (bool) $valueStr;
    }

    /**
     * @todo Remove.
     */
    public function getIntSetting($id): int
    {
        $valueStr = $this->appConfigRepo->get($id) ?? $this->defaultConfigArray[$id];
        return intval($valueStr);
    }

    /**
     * @todo Remove.
     */
    public function getStringSetting($id): string
    {
        return $this->appConfigRepo->get($id) ?? $this->defaultConfigArray[$id];
    }

    public function getSetting(string $id, string $expectedType)
    {
        $value = unserialize($this->get($id));
        $this->checkType($value, $expectedType);

        return $value;
    }

    /**
     * @todo Use a more specific exception.
     */
    public function set(string $id, Serializable $value): self
    {
        $this->appConfigRepo->set($id, serialize($value));

        return $this;
    }

    public function toJson(): string
    {
        $configArray = [];
        foreach (Setting::getKeys() as $currentKey) {
            $configArray[$currentKey] = $this->get($currentKey);
        }

        return json_encode($configArray);
    }

    /**
     * @todo Exception
     */
    public function fromJson(string $json): void
    {
        $configArray = json_decode($json, true);
        if (null === $configArray) {
            throw new Exception();
        }
        foreach (Setting::getKeys() as $currentKey) {
            if (true === isset($configArray[$currentKey])) {
                $this->set($currentKey, $configArray[$currentKey]);
            }
        }
    }
}
