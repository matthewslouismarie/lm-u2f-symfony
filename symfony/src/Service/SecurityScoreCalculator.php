<?php

namespace App\Service;

use InvalidArgumentException;

class SecurityScoreCalculator
{
    const ACCESS_RESISTANCE_MAX = 1;

    const GUESS_RESISTANCE_MAX = 1;

    const PHISHING_RESISTANCE_MAX = 1;

    const SERVER_LEAK_RESISTANCE_MAX = 1;

    const REPRODUCIBILITY_RESISTANCE_MAX = 1;

    const PWD_OFFLINE_FACTOR = 10;

    private $challengeDefs;

    public function __construct()
    {
        $this->challengeDefs = [
            'pwd',
            'u2f',
        ];
    }

    public function calculate(array $processes): float
    {
        $scores = [];
        foreach ($processes as $process) {
            $scores[] =  $this->calculateProcessScore($process, 0);
        }

        return min($scores);
    }

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

        if (false === is_array($challenge)) {
            throw new InvalidArgumentException();
        }

        return $this->calculateProcessScore(
            $process,
            $currentScore + $nFactors * $this->calculateChallengeScore($challenge)
        );
    }

    /**
     * @todo Change type for id.
     * @todo u2f: reproducible: depends on the key.
     * @todo pwd: can be different accross website, or not. We'll assume they're
     * not that different.
     */
    public function calculateChallengeScore(array $challenge): float
    {
        if ('u2f' === $challenge['id']) {
            $guessResistance = 1 * self::GUESS_RESISTANCE_MAX;
            $notReproducible = 0.5 * self::REPRODUCIBILITY_RESISTANCE_MAX;
            $phishingResistance = 1 * self::PHISHING_RESISTANCE_MAX;
            $accessResistance = 0.2 * self::ACCESS_RESISTANCE_MAX;
            $serverLeakResistance = 1 * self::SERVER_LEAK_RESISTANCE_MAX;
        } elseif ('pwd' === $challenge['id']) {
            $guessResistance = $this->calculatePwdGuessResistance(
                $challenge['min_length'],
                $challenge['special_chars'],
                $challenge['numbers'],
                $challenge['uppercase'],
                true
            ) * self::GUESS_RESISTANCE_MAX;
            $notReproducible = 0 * self::REPRODUCIBILITY_RESISTANCE_MAX;
            $phishingResistance = 0 * self::PHISHING_RESISTANCE_MAX;
            $accessResistance = 0.7 * self::ACCESS_RESISTANCE_MAX;
            $serverLeakResistance = $this->calculatePwdGuessResistance(
                $challenge['min_length'],
                $challenge['special_chars'],
                $challenge['numbers'],
                $challenge['uppercase'],
                false
            ) * self::SERVER_LEAK_RESISTANCE_MAX;
        } else {
            throw new InvalidArgumentException();
        }

        return 
            $notReproducible +
            $guessResistance +
            $phishingResistance +
            $accessResistance +
            $serverLeakResistance
        ;
    }

    public function getNFactors(array $process): float
    {
        $types = [];
        $nFactors = 0;
        foreach ($process as $challenge) {
            if (
                !is_array($challenge) ||
                !isset($challenge['id']) ||
                !is_string($challenge['id'])
            ) {
                throw new InvalidArgumentException();
            }
            if (in_array($challenge['id'], $types, true)) {
                $nFactors += $this->getDuplicateChallengeFactor($challenge['id']);
            } else {
                $nFactors += 1;
            }
            $types[] = $challenge['id'];
        }

        return $nFactors;
    }

    private function calculatePwdGuessResistance(
        int $minLength,
        bool $specialChars,
        bool $numbers,
        bool $uppercase,
        bool $online
    ): float {
        $factorSpecialChars = $specialChars ? 0.1 : 0;
        $factorNumbers = $numbers ? 0.1 : 0;
        $factorUppercase = $uppercase ? 0.1 : 0;
        $onlineFactor = $online ? 1 : self::PWD_OFFLINE_FACTOR;
        $factor = ($factorSpecialChars + $factorNumbers + $factorUppercase) / $onlineFactor;

        return (-1/(sqrt($factor * $minLength) + 1) + 1);
    }

    public function getDuplicateChallengeFactor(string $challenge): float
    {
        if ('u2f' === $challenge) {
            return 0.8;
        } elseif ('pwd' === $challenge) {
            return 0;
        }
    }
}
