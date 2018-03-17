<?php

namespace App\FormModel;

use Symfony\Component\Validator\Constraints as Assert;

class SecurityStrategySubmission
{
    /**
     * @Assert\Type("int")
     */
    public $securityStrategyId;

    public function __construct($securityStrategyId)
    {
        $this->securityStrategyId = $securityStrategyId;
    }
}
