<?php

namespace App\FormModel;

use App\Validator\Constraints\AvailableUsername;
use App\Validator\Constraints\ValidNewPassword;
use Serializable;
use Symfony\Component\Validator\Constraints as Assert;

class CredentialRegistrationSubmission implements Serializable
{
    /**
     * @AvailableUsername
     * @Assert\NotBlank()
     * @Assert\Type("string")
     */
    private $username;

    /**
     * @ValidNewPassword
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

    public function serialize(): string
    {
        return serialize([
            $this->password,
            $this->passwordConfirmation,
            $this->username,
        ]);
    }

    public function unserialize($serialized): void
    {
        list(
            $this->password,
            $this->passwordConfirmation,
            $this->username) = unserialize($serialized);
    }
}
