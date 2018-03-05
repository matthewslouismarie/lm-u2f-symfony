<?php

namespace App\FormModel;

use Symfony\Component\Validator\Constraints as Assert;

class RegistrationConfigSubmission
{
    /**
     * @Assert\GreaterThanOrEqual(0)
     * @Assert\Type(type="integer")
     */
    private $nU2fKeys;

    public function __construct(?int $nU2fKeys = null)
    {
        $this->nU2fKeys = $nU2fKeys;
    }

    public function getNU2fKeys(): ?int
    {
        return $this->nU2fKeys;
    }

    public function setNU2fKeys(?int $nU2fKeys): void
    {
        $this->nU2fKeys = $nU2fKeys;
    }
}
