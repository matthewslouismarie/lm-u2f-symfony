<?php

namespace App\Enum;

use ReflectionClass;

/**
 * @todo Probably something to refactor here.
 */
class SecurityStrategy
{
    const U2F = 0;

    const PWD = 1;

    public static function getIds(): array
    {
        $reflection = new ReflectionClass(__CLASS__);
        return $reflection->getConstants();
    }
}
