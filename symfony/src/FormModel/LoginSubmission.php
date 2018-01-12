<?php

namespace App\FormModel;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @todo Is it a good thing that this class is able to contain invalid data?
 */
class LoginSubmission
{
    /**
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

    public function setUsername(?string $username): void
    {
        $this->username = $username;
    }

    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }
}
