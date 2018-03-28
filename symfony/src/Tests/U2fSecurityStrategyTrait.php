<?php

namespace App\Tests;

use App\Enum\Setting;

trait U2fSecurityStrategyTrait
{
    public function activateU2fSecurityStrategy()
    {
        $this
            ->getAppConfigManager()
            ->set(Setting::N_U2F_KEYS_REG, 1)
            ->set(Setting::PWD_NUMBERS, false)
            ->set(Setting::PWD_SPECIAL_CHARS, false)
            ->set(Setting::PWD_UPPERCASE, false)
            ->set(Setting::PWD_ENFORCE_MIN_LENGTH, false)
        ;
    }
}
