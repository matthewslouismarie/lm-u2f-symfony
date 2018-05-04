<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\IChallengeDefinition;
use App\Model\U2fChallengeDefinition;
use App\Model\PwdChallengeDefinition;
use App\Model\SmsChallengeDefinition;
use App\Enum\Setting;
use LM\Common\Enum\Scalar;
use stdClass;

/**
 * @todo Coupled with all the IChallengeDefinition implementations… not great.
 * @todo Rename to …Factory
 */
final class SecurityStrategyUnserializer
{
    private $config;

    public function __construct(AppConfigManager $config)
    {
        $this->config = $config;
    }

    /**
     * @todo Unit test.
     */
    public function unserialize(array $inputArray): array
    {
        return array_map(
            function (array $process): array {
                return array_map(
                    function (stdClass $serChallenge): IChallengeDefinition {
                        switch ($serChallenge->type) {
                            case 'u2f':
                                return new U2fChallengeDefinition();

                            case 'pwd':
                                return new PwdChallengeDefinition(
                                    $serChallenge->min_length,
                                    $serChallenge->numbers,
                                    $serChallenge->special_chars,
                                    $serChallenge->uppercase
                                );

                            case 'sms':
                                return new SmsChallengeDefinition();

                            default:
                                throw new UnexpectedValueException();
                        }
                    },
                    $process
                );
            },
            $inputArray
        );
    }

    public function fromAppConfig(): array
    {
        $minLength = $this->config->get(Setting::PWD_ENFORCE_MIN_LENGTH, Scalar::_BOOL) ? $this->config->get(Setting::PWD_MIN_LENGTH, Scalar::_INT) : 0;
        $numbers = $this->config->get(Setting::PWD_NUMBERS, Scalar::_INT);
        $specialChars = $this->config->get(Setting::PWD_SPECIAL_CHARS, Scalar::_INT);
        $uppercase = $this->config->get(Setting::PWD_UPPERCASE, Scalar::_INT);
        $pwdChallenge = new PwdChallengeDefinition($minLength, $numbers, $specialChars, $uppercase);
        $pwdProcess = [
            $pwdChallenge,
        ];
        $spwdU2fProcess = [];
        $spwdU2fProcess[] = $pwdChallenge;
        $nU2fRegistrations = $this->config->get(Setting::N_U2F_KEYS_LOGIN, Scalar::_INT);
        for ($i = 0; $i < $nU2fRegistrations; $i++) {
            $spwdU2fProcess[] = new U2fChallengeDefinition();
        }
        $allowU2fLogin = $this->config->get(Setting::ALLOW_U2F_LOGIN, Scalar::_BOOL);
        $allowPwdLogin = $this->config->get(Setting::ALLOW_PWD_LOGIN, Scalar::_BOOL);
        if ($allowU2fLogin && $allowPwdLogin) {
            return [
                $pwdProcess,
                $spwdU2fProcess,
            ];
        } elseif ($allowU2fLogin) {
            return [
                $spwdU2fProcess,
            ];
        } elseif ($allowPwdLogin) {
            return [
                $pwdProcess,
            ];
        } else {
            return [];
        }
    }
}
