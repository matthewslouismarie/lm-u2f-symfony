<?php

namespace App\Model;

use InvalidArgumentException;
use UnexpectedValueException;

class TransitingData
{
    private $key;

    private $route;

    private $value;

    public function __construct(
        string $key,
        string $route,
        $value
    ) {
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

    public function getValue(string $class)
    {
        if (get_class($this->value) === $class) {
            return $this->value;
        } else {
            echo "\n Expected: ".get_class($this->value).", but got: ".$class."\n";
            throw new UnexpectedValueException();
        }
    }

    public function isAlike($attribute, $value): bool
    {
        switch ($attribute) {
            case 'class':
                return $this->getClass() === $value;

            case 'key':
                return $this->getKey() === $value;

            case 'route':
                return $this->getRoute() === $value;

            default:
                throw new InvalidArgumentException();
        }
    }
}
