<?php

namespace App\FormModel;

use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints\IdenticalPassword;

/**
 * @todo Rename to UpRegistrationSubmission.
 */
class CredentialRegistrationSubmission
{
    /**
     * @todo check it's not already used
     * @Assert\NotBlank()
     * @Assert\Type("string")
     */
    private $username;

    /**
     * @Assert\NotBlank()
     * @Assert\Type("string")
     */
    private $password;

    /**
     * @Assert\IdenticalTo(
     *  propertyPath="password",
     *  message="Your password confirmation does not match.")
     * @Assert\Type("string")
     */
    private $passwordConfirmation;

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getPasswordConfirmation(): ?string
    {
        return $this->passwordConfirmation;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function setPasswordConfirmation(?string $passwordConfirmation): void
    {
        $this->passwordConfirmation = $passwordConfirmation;
    }
}
