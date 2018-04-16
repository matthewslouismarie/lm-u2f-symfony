<?php

namespace App\FormModel;

use App\Validator\Constraints\ValidNewPassword;
use Symfony\Component\Validator\Constraints as Assert;

class PasswordUpdateSubmission
{
    /**
     * @ValidNewPassword
     * @Assert\NotBlank()
     * @Assert\Type("string")
     */
    public $password;

    /**
     * @Assert\IdenticalTo(
     *  propertyPath="password",
     * message="The password confirmation does not match")
     * @Assert\Type("string")
     */
    private $passwordConfirmation;

    public function __construct(
        ?string $password = null,
        ?string $passwordConfirmation = null
    ) {
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
