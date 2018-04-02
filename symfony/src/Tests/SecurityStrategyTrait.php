<?php

namespace App\Tests;

use App\Enum\SecurityStrategy;
use App\Enum\Setting;

trait SecurityStrategyTrait
{
    public function activatePwdSecurityStrategy()
    {
        $this
            ->getAppConfigManager()
            ->set(Setting::N_U2F_KEYS_REG, 0)
            ->set(Setting::N_U2F_KEYS_POST_AUTH, 0)
            ->set(Setting::ALLOW_PWD_LOGIN, true)
            ->set(Setting::ALLOW_U2F_LOGIN, false)
            ->set(Setting::SECURITY_STRATEGY, SecurityStrategy::PWD)
            ->set(Setting::ALLOW_MEMBER_TO_MANAGE_U2F_KEYS, false)
            ->set(Setting::PWD_MIN_LENGTH, 5)
            ->set(Setting::PWD_NUMBERS, true)
            ->set(Setting::PWD_SPECIAL_CHARS, true)
            ->set(Setting::PWD_UPPERCASE, true)
            ->set(Setting::PWD_ENFORCE_MIN_LENGTH, true)
        ;
    }

    public function activateU2fSecurityStrategy()
    {
        $this
            ->getAppConfigManager()
            ->set(Setting::N_U2F_KEYS_REG, 1)
            ->set(Setting::N_U2F_KEYS_POST_AUTH, 1)
            ->set(Setting::ALLOW_PWD_LOGIN, false)
            ->set(Setting::ALLOW_U2F_LOGIN, true)
            ->set(Setting::SECURITY_STRATEGY, SecurityStrategy::U2F)
            ->set(Setting::ALLOW_MEMBER_TO_MANAGE_U2F_KEYS, false)
            ->set(Setting::PWD_NUMBERS, false)
            ->set(Setting::PWD_SPECIAL_CHARS, false)
            ->set(Setting::PWD_UPPERCASE, false)
            ->set(Setting::PWD_ENFORCE_MIN_LENGTH, false)
        ;
    }
}
