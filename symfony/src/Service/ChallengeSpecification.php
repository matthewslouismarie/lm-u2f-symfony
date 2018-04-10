<?php

namespace App\Service;

use App\Enum\SecurityStrategy;
use App\Enum\Setting;
use App\Service\AppConfigManager;
use LM\Authentifier\Challenge\CredentialChallenge;
use LM\Authentifier\Challenge\ExistingUsernameChallenge;
use LM\Authentifier\Challenge\PasswordChallenge;
use LM\Authentifier\Challenge\U2fChallenge;
use LM\Common\Enum\Scalar;
use LM\Common\Model\ArrayObject;
use UnexpectedValueException;

class ChallengeSpecification
{
    private $config;

    public function __construct(AppConfigManager $config)
    {
        $this->config = $config;
    }

    public function getChallenges(?string $username = null): ArrayObject
    {
        if (null === $username) {
            if ($this->config->getBoolSetting(Setting::ALLOW_PWD_LOGIN)) {
                return new ArrayObject([
                    CredentialChallenge::class,
                ], Scalar::_STR);
            } else {
                return new ArrayObject([
                    ExistingUsernameChallenge::class,
                    U2fChallenge::class,
                ], Scalar::_STR);
            }
        } else {
            switch ($this->config->getSetting(Setting::SECURITY_STRATEGY, Scalar::_STR)) {
                case SecurityStrategy::U2F:
                    return new ArrayObject([
                        U2fChallenge::class,
                    ], Scalar::_STR);
                    break;

                case SecurityStrategy::PWD:
                    return new ArrayObject([
                        PasswordChallenge::class,
                    ], Scalar::_STR);
                    break;

                default:
                    throw new UnexpectedValueException();
            }
        }
    }
}
