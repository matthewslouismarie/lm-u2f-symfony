<?php

namespace App\Service;

use App\Model\IChallengeDefinition;
use App\Model\U2fChallengeDefinition;
use App\Model\PwdChallengeDefinition;
use stdClass;

/**
 * @todo Coupled with all the IChallengeDefinition implementations… not great.
 */
final class SecurityStrategyUnserializer
{
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
}
