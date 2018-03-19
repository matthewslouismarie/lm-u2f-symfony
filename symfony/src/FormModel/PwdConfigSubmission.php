<?php

namespace App\FormModel;

use Symfony\Component\Validator\Constraints as Assert;

class PwdConfigSubmission
{
    /**
     * @Assert\Type("bool")
     */
    public $forceComplexPasswords;

    public function __construct(
        ?bool $forceComplexPasswords = null)
    {
        $this->forceComplexPasswords = $forceComplexPasswords;
    }
}
