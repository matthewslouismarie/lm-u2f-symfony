<?php

namespace App\FormModel;

use Symfony\Component\Validator\Constraints as Assert;

class U2fConfigSubmission
{
    /**
     * @Assert\Type("bool")
     */
    public $allowU2fLogin;

    /**
     * @Assert\GreaterThanOrEqual(0)
     * @Assert\Type("integer")
     */
    public $nU2fKeysPostAuth;

    /**
     * @Assert\GreaterThanOrEqual(0)
     * @Assert\Type("integer")
     */
    public $nU2fKeysReg;

    public function __construct(
        ?bool $allowU2fLogin = null,
        ?int $nU2fKeysPostAuth = null,
        ?int $nU2fKeysReg = null)
    {
        $this->allowU2fLogin = $allowU2fLogin;
        $this->nU2fKeysPostAuth = $nU2fKeysPostAuth;
        $this->nU2fKeysReg = $nU2fKeysReg;
    }
}
