<?php

namespace App\FormModel;

use Symfony\Component\Validator\Constraints as Assert;

class PwdConfigSubmission
{
    /**
     * @Assert\Type("integer")
     */
    public $minimumLength;

    /**
     * @Assert\Type("bool")
     */
    public $requireNumbers;

    /**
     * @Assert\Type("bool")
     */
    public $requireSpecialCharacters;

    /**
     * @Assert\Type("bool")
     */
    public $requireUppercaseLetters;

    /**
     * @Assert\Type("bool")
     */
    public $forceComplexPasswords;

    public function __construct(
        ?int $minimumLength = null,
        ?bool $requireNumbers = null,
        ?bool $requireSpecialCharacters = null,
        ?bool $requireUppercaseLetters = null)
    {
        $this->minimumLength = $minimumLength;
        $this->requireNumbers = $requireNumbers;
        $this->requireSpecialCharacters = $requireSpecialCharacters;
        $this->requireUppercaseLetters = $requireUppercaseLetters;
    }
}
