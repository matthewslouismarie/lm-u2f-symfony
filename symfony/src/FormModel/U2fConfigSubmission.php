<?php

declare(strict_types=1);

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

    /**
     * @Assert\Type("bool")
     */
    public $allowMemberToManageU2fKeys;

    public function __construct(
        ?bool $allowU2fLogin = null,
        ?int $nU2fKeysPostAuth = null,
        ?int $nU2fKeysReg = null,
        ?bool $allowMemberToManageU2fKeys = null
    ) {
        $this->allowU2fLogin = $allowU2fLogin;
        $this->nU2fKeysPostAuth = $nU2fKeysPostAuth;
        $this->nU2fKeysReg = $nU2fKeysReg;
        $this->allowMemberToManageU2fKeys = $allowMemberToManageU2fKeys;
    }
}
