<?php

namespace App\FormModel;

use Symfony\Component\Validator\Constraints as Assert;

class PasswordUpdateSubmission
{
    /**
     * @Assert\NotBlank()
     */
    private $password;

    /**
     * @Assert\IdenticalTo(propertyPath="password")
     */
    private $passwordConfirmation;

    public function __construct(
        ?string $password = null,
        ?string $passwordConfirmation = null)
    {
        $this->password = $password;
        $this->passwordConfirmation = $passwordConfirmation;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getPasswordConfirmation(): ?string
    {
        return $this->passwordConfirmation;
    }

    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    public function setPasswordConfirmation(?string $passwordConfirmation): void
    {
        $this->passwordConfirmation = $passwordConfirmation;
    }
}
