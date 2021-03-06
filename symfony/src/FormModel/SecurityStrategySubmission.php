<?php

declare(strict_types=1);

namespace App\FormModel;

use Symfony\Component\Validator\Constraints as Assert;

class SecurityStrategySubmission
{
    /**
     * @Assert\NotBlank()
     */
    public $securityStrategyId;

    public function __construct($securityStrategyId)
    {
        $this->securityStrategyId = $securityStrategyId;
    }
}
