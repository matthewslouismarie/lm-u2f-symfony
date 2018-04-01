<?php

namespace App\Enum;

/**
 * @todo Rename to SettingKey?
 */
class Setting
{
    const N_U2F_KEYS_REG = "0";

    const N_U2F_KEYS_POST_AUTH = "1";

    const ALLOW_U2F_LOGIN = "2";

    const SECURITY_STRATEGY = "3";

    const ALLOW_MEMBER_TO_MANAGE_U2F_KEYS = "4";

    const PWD_MIN_LENGTH = "5";

    const PWD_NUMBERS = "6";

    const PWD_SPECIAL_CHARS = "7";

    const PWD_UPPERCASE = "8";

    const PWD_ENFORCE_MIN_LENGTH = "9";

    const USER_STUDY_MODE_ACTIVE = 'IS_USER_STUDY_MODE_ACTIVE';

    const PARTICIPANT_ID = "PARTICIPANT_ID";
}
