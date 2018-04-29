<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\IChallengeDefinition;
use InvalidArgumentException;

class SecurityScoreCalculator
{
    const ACCESS_RESISTANCE_MAX = 1;

    const GUESS_RESISTANCE_MAX = 1;

    const PHISHING_RESISTANCE_MAX = 1;

    const SERVER_LEAK_RESISTANCE_MAX = 1;

    const REPRODUCIBILITY_RESISTANCE_MAX = 1;

    /**
     * @param array[] The security strategy, it contains authentication
     * processes.
     * @return float The security score of the security strategy.
     */
    public function calculate(array $securityStrategy): float
    {
        $scores = [];
        foreach ($securityStrategy as $process) {
            $scores[] =  $this->calculateProcessScore($process, 0);
        }

        return min($scores);
    }

    /**
     * @param array $process The authentication process.
     * @param float $currentScore The current score so far.
     * @param int|null $nFactors The number of factors in the authentication
     * process, null if not calculated yet.
     * @return float The security score of the authentication store.
     */
    public function calculateProcessScore(
        array $process,
        float $currentScore = 0.0,
        ?int $nFactors = null
    ): float {
        if (true === empty($process)) {
            return $currentScore;
        }
        if (null === $nFactors) {
            $nFactors = $this->getNFactors($process);
        }
        $challenge = array_pop($process);

        if (false === $challenge instanceof IChallengeDefinition) {
            throw new InvalidArgumentException();
        }

        return $this->calculateProcessScore(
            $process,
            $currentScore + $nFactors * $this->calculateChallengeScore($challenge)
        );
    }

    /**
     * @param IChallengeDefinition $challenge The authentication challenge.
     * @return float The security score of the authentication challenge.
     */
    public function calculateChallengeScore(IChallengeDefinition $challenge): float
    {
        return
            $challenge->getAccessResistance() * self::ACCESS_RESISTANCE_MAX +
            $challenge->getGuessResistance() * self::GUESS_RESISTANCE_MAX +
            $challenge->getPhishingResistance() * self::PHISHING_RESISTANCE_MAX +
            $challenge->getReproducibilityResistance() * self::REPRODUCIBILITY_RESISTANCE_MAX +
            $challenge->getServerLeakResistance() * self::SERVER_LEAK_RESISTANCE_MAX
        ;
    }

    /**
     * @param array $process An authentication process.
     * @return float The security score of the authentication process.
     */
    public function getNFactors(array $process): float
    {
        $types = [];
        $nFactors = 0;
        foreach ($process as $challenge) {
            if (false === $challenge instanceof IChallengeDefinition) {
                throw new InvalidArgumentException();
            }
            if (in_array($challenge->getType(), $types, true)) {
                $nFactors += $challenge->getDuplicationFactor();
            } else {
                $nFactors += 1;
            }
            $types[] = $challenge->getType();
        }

        return $nFactors;
    }
}
