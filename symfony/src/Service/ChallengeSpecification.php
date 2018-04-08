<?php

namespace App\Service;

use App\Enum\Setting;
use App\Service\AppConfigManager;
use LM\Authentifier\Challenge\PasswordChallenge;
use LM\Authentifier\Challenge\U2fChallenge;
use LM\Common\Model\ArrayObject;

class ChallengeSpecification
{
    private $config;

    public function __construct(AppConfigManager $config)
    {
        $this->config = $config;
    }

    public function getChallenges(): ArrayObject
    {
        if ($this->config->getBoolSetting(Setting::ALLOW_PWD_LOGIN)) {
            return new ArrayObject([
                PasswordChallenge::class,
            ], 'string');
        } else {
            return new ArrayObject([
                U2fChallenge::class,
            ], 'string');
        }
    }
}
