<?php

declare(strict_types=1);

namespace App\Model;

/**
 * @todo u2f: reproducible: depends on the key.
 * @todo Unit test.
 */
final class SmsChallengeDefinition implements IChallengeDefinition
{
    public function getAccessResistance(): float
    {
        return 0.8;
    }

    public function getDuplicationFactor(): float
    {
        return 0.6;
    }

    public function getGuessResistance(): float
    {
        return 1;
    }

    public function getPhishingResistance(): float
    {
        return 0.2;
    }

    public function getReproducibilityResistance(): float
    {
        return 1;
    }

    public function getServerLeakResistance(): float
    {
        return 0.3;
    }

    public function getType(): string
    {
        return 'sms';
    }

    public function jsonSerialize()
    {
        return [
            'type' => $this->getType(),
        ];
    }
}
