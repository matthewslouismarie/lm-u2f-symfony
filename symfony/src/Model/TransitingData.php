<?php

namespace App\Model;

use Serializable;

class TransitingData
{
    private $key;

    private $route;

    private $value;

    public function __construct(
        string $key,
        string $route,
        Serializable $value)
    {
        $this->key = $key;
        $this->route = $route;
        $this->value = $value;
    }

    public function getClass(): string
    {
        return get_class($this->value);
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getRoute(): string
    {
        return $this->route;
    }

    public function getValue(): Serializable
    {
        return $this->value;
    }

    public function isAlike($attribute, $value): bool
    {
        switch ($attribute) {
            case 'class':
                return $this->getClass() === $value;
                break;

            case 'key':
                return $this->getKey() === $value;
                break;

            case 'route':
                return $this->getRoute() === $value;
                break;

            default:
                throw new InvalidArgumentException();
        }
    }
}
