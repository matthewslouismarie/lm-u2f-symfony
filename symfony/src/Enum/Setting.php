<?php

namespace App\Enum;

use ReflectionClass;

/**
 * @todo Rename to SettingKey?
 */
class Setting
{
    const ALLOW_MEMBER_TO_MANAGE_U2F_KEYS = "ALLOW_MEMBER_TO_MANAGE_U2F_KEYS";

    const ALLOW_PWD_LOGIN = "ALLOW_PWD_LOGIN";

    const ALLOW_U2F_LOGIN = "ALLOW_U2F_LOGIN";

    const N_U2F_KEYS_LOGIN = "N_U2F_KEYS_LOGIN";

    const N_U2F_KEYS_POST_AUTH = "N_U2F_KEYS_POST_AUTH";

    const N_U2F_KEYS_REG = "N_U2F_KEYS_REG";

    const PARTICIPANT_ID = "PARTICIPANT_ID";

    const PWD_MIN_LENGTH = "PWD_MIN_LENGTH";

    const PWD_NUMBERS = "PWD_NUMBERS";

    const PWD_SPECIAL_CHARS = "PWD_SPECIAL_CHARS";

    const PWD_UPPERCASE = "PWD_UPPERCASE";

    const PWD_ENFORCE_MIN_LENGTH = "PWD_ENFORCE_MIN_LENGTH";

    const SEC_HIGH_PWD = 'sec_high_pwd';

    const SEC_HIGH_U2F = 'sec_high_u2f';

    const SEC_HIGH_U2F_N = 'sec_high_u2f_n';

    const SEC_HIGH_BOTH = 'sec_high_both';

    const SEC_MEDM_PWD = 'sec_medm_pwd';

    const SEC_MEDM_U2F = 'sec_medm_u2f';

    const SEC_MEDM_U2F_N = 'sec_medm_u2f_n';

    const SEC_MEDM_BOTH = 'sec_medm_both';

    const SECURITY_STRATEGY = "SECURITY_STRATEGY";

    const USER_STUDY_MODE_ACTIVE = 'IS_USER_STUDY_MODE_ACTIVE';

    public static function getKeys(): array
    {
        $reflection = new ReflectionClass(__CLASS__);
        return $reflection->getConstants();
    }
}
