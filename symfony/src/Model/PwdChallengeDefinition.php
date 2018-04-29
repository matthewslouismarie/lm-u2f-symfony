<?php

declare(strict_types=1);

namespace App\Model;

use InvalidArgumentException;

/**
 * @todo pwd: can be different accross website, or not. We'll assume they're
 * not that different.
 */
final class PwdChallengeDefinition implements IChallengeDefinition
{
    const OFFLINE_FACTOR = 10;

    const ONLINE_FACTOR = 1;

    private $minLength;

    private $numbers;

    private $specialChars;

    private $uppercase;

    public function __construct(
        int $minLength,
        bool $numbers,
        bool $specialChars,
        bool $uppercase
    ) {
        if (0 > $minLength) {
            throw new InvalidArgumentException();
        }
        $this->minLength = $minLength;
        $this->numbers = $numbers;
        $this->specialChars = $specialChars;
        $this->uppercase = $uppercase;
    }

    public function getAccessResistance(): float
    {
        return 0.3;
    }

    public function getDuplicationFactor(): float
    {
        return 0;
    }

    public function getGuessResistance(): float
    {
        $factorSpecialChars = $this->specialChars ? 0.1 : 0;
        $factorNumbers = $this->numbers ? 0.1 : 0;
        $factorUppercase = $this->uppercase ? 0.1 : 0;
        $factor = ($factorSpecialChars + $factorNumbers + $factorUppercase) / self::ONLINE_FACTOR;

        return (-1/(sqrt($factor * $this->minLength) + 1) + 1);
    }

    public function getPhishingResistance(): float
    {
        return 0;
    }

    public function getReproducibilityResistance(): float
    {
        return 0;
    }

    public function getServerLeakResistance(): float
    {
        $factorSpecialChars = $this->specialChars ? 0.1 : 0;
        $factorNumbers = $this->numbers ? 0.1 : 0;
        $factorUppercase = $this->uppercase ? 0.1 : 0;
        $factor = ($factorSpecialChars + $factorNumbers + $factorUppercase) / self::OFFLINE_FACTOR;

        return (-1/(sqrt($factor * $this->minLength) + 1) + 1);
    }

    public function getType(): string
    {
        return 'pwd';
    }
}
