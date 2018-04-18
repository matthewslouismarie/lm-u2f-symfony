<?php

namespace App\Service;

use App\Enum\SecurityStrategy;
use App\Enum\Setting;
use LM\Authentifier\Challenge\CredentialChallenge;
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

    public function getChallenges(
        ?string $username = null,
        array $preChallenges = [],
        array $postChallenges = []): ArrayObject
    {
        $challenges = $preChallenges;

        if (null === $username) {
            if ($this->config->getBoolSetting(Setting::ALLOW_PWD_LOGIN)) {
                $challenges[] = CredentialChallenge::class;
            } else {
                $challenges[] = CredentialChallenge::class;
                $challenges[] = U2fChallenge::class;
            }
        } else {
            switch ($this->config->getSetting(Setting::SECURITY_STRATEGY, Scalar::_STR)) {
                case SecurityStrategy::U2F:
                    $challenges[] = PasswordChallenge::class;
                    $challenges[] = U2fChallenge::class;
                    break;

                case SecurityStrategy::PWD:
                    $challenges[] = PasswordChallenge::class;
                    break;

                default:
                    throw new UnexpectedValueException();
            }
        }
        $challenges = array_merge($challenges, $postChallenges);

        return new ArrayObject($challenges, Scalar::_STR);
    }
}
