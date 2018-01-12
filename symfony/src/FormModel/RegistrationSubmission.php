<?php

namespace App\FormModel;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @todo Maybe methods shouldn't be type-hinted?
 */
class RegistrationSubmission
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

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }
}
