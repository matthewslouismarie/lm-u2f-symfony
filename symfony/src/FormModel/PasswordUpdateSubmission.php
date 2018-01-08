<?php

namespace App\FormModel;

use Symfony\Component\Validator\Constraints as Assert;

class PasswordUpdateSubmission
{
    /**
     * @Assert\NotBlank()
     */
    private $password;

    public function __construct(?string $password = null)
    {
        $this->password = $password;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }
}