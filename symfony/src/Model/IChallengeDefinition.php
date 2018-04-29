<?php

declare(strict_types=1);

namespace App\Model;

/**
 * Represents a challenge type (e.g. the password challenge type, the U2F
 * challenge type, the mobile authentication challenge type, etc.) in order
 * to calculate a security score.
 */
interface IChallengeDefinition
{
    /**
     * @return float Depicts how hard it is to get access to the authentifier.
     * 0 means that it could not be easier, and 1 that it is impossible.
     * Implementations MUST return a value between 0 and 1.
     */
    public function getAccessResistance(): float;

    /**
     * @return float A factor is not exactly a type. Two challenges of the same
     * type can count as more than one factor. The value returned depicts how
     * much additional security does the process of adding a second challenge
     * of this type to an authentication process increases the security of the
     * authentication process, or rather, how does a challenge's security
     * remains unaffected if a challenge of the same type is compromised in the
     * same authentication process. 1 is completely unaffected, and 0 is
     * completely compromised. Implementations MUST return a value between 0 and
     * 1.
     */
    public function getDuplicationFactor(): float;

    /**
     * @return float The resistance to online brute-force attacks. 0 means
     * challenges of this type offer no resistance at all, 1 means they offer
     * complete protection. Implementations MUST return a value between 0 and 1.
     */
    public function getGuessResistance(): float;

    /**
     * @return float The resistance to phishing attacks. 0 means challenges of
     * this type offer no resistance at all, 1 means they offer complete
     * protection. Implementations MUST return a value between 0 and 1.
     */
    public function getPhishingResistance(): float;

    /**
     * @return float Represents how hard it is to reproduce the authentifier
     * given access to it. 0 means challenges of this type offer no resistance
     * at all, 1 means they offer complete protection. Implementations MUST
     * return a value between 0 and 1.
     */
    public function getReproducibilityResistance(): float;

    /**
     * @return float The resistance to server information leakage. 0 means
     * challenges of this type offer no resistance at all, 1 means they offer
     * complete protection. Implementations MUST return a value between 0 and 1.
     */
    public function getServerLeakResistance(): float;

    /**
     * @return string The type of the challenge ("pwd", "u2f"). Implementations
     * MUST ensure all its instances always return the exact same string, and
     * MUST return a string that is not already used by other implementations
     * in the same application.
     */
    public function getType(): string;
}
