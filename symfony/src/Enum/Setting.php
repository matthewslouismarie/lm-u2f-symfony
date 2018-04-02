<?php

namespace App\Enum;

use ReflectionClass;

/**
 * @todo Rename to SettingKey?
 */
class Setting
{
    const N_U2F_KEYS_REG = "N_U2F_KEYS_REG";

    const N_U2F_KEYS_POST_AUTH = "N_U2F_KEYS_POST_AUTH";

    const ALLOW_PWD_LOGIN = "ALLOW_PWD_LOGIN";

    const ALLOW_U2F_LOGIN = "ALLOW_U2F_LOGIN";

    const SECURITY_STRATEGY = "SECURITY_STRATEGY";

    const ALLOW_MEMBER_TO_MANAGE_U2F_KEYS = "ALLOW_MEMBER_TO_MANAGE_U2F_KEYS";

    const PWD_MIN_LENGTH = "PWD_MIN_LENGTH";

    const PWD_NUMBERS = "PWD_NUMBERS";

    const PWD_SPECIAL_CHARS = "PWD_SPECIAL_CHARS";

    const PWD_UPPERCASE = "PWD_UPPERCASE";

    const PWD_ENFORCE_MIN_LENGTH = "PWD_ENFORCE_MIN_LENGTH";

    const USER_STUDY_MODE_ACTIVE = 'IS_USER_STUDY_MODE_ACTIVE';

    const PARTICIPANT_ID = "PARTICIPANT_ID";

    public static function getKeys(): array
    {
        $reflection = new ReflectionClass(__CLASS__);
        return $reflection->getConstants();
    }
}
