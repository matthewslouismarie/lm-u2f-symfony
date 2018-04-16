<?php

namespace App\Service;

use App\Enum\Setting;
use App\Exception\NonexistentSettingException;
use App\Repository\AppSettingRepository;
use Exception;
use LM\Common\Enum\Scalar;
use LM\Common\Type\TypeCheckerTrait;
use Serializable;

/**
 * @todo Rename to GlobalConfig.
 */
class AppConfigManager
{
    use TypeCheckerTrait;

    private $appConfigRepo;

    /**
     * @todo More specific exception.
     */
    public function __construct(AppSettingRepository $appConfigRepo)
    {
        $this->appConfigRepo = $appConfigRepo;
    }

    public function get(string $id)
    {
        $appSetting = $this
            ->appConfigRepo
            ->find($id)
        ;
        if (null === $appSetting) {
            throw new NonexistentSettingException();
        }

        return $appSetting->getValue();
    }

    /**
     * @todo Remove.
     */
    public function getBoolSetting($id): bool
    {
        return $this->getSetting($id, Scalar::_BOOL);
    }

    /**
     * @todo Remove.
     */
    public function getIntSetting($id): int
    {
        return $this->getSetting($id, Scalar::_INT);
    }

    /**
     * @todo Remove.
     */
    public function getStringSetting($id): string
    {
        return $this->getSetting($id, Scalar::_STR);
    }

    public function getSetting(string $id, string $expectedType)
    {
        $value = $this->get($id);
        $this->checkType($value, $expectedType);

        return $value;
    }

    /**
     * @todo Use a more specific exception.
     * @todo Remove?
     */
    public function set(string $id, $value): self
    {
        $this->appConfigRepo->set($id, $value);

        return $this;
    }

    /**
     * @todo Use a more specific exception.
     */
    public function setObject(string $id, Serializable $value): self
    {
        $this->appConfigRepo->set($id, $value);

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
