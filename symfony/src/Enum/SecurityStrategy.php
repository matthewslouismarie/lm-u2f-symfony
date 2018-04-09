<?php

namespace App\Enum;

use ReflectionClass;

/**
 * @todo Probably something to refactor here.
 */
class SecurityStrategy
{
    const U2F = 'U2F';

    const PWD = 'PWD';

    public static function getIds(): array
    {
        $reflection = new ReflectionClass(__CLASS__);
        return $reflection->getConstants();
    }
}
