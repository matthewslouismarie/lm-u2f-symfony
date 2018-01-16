<?php

namespace App\FormModel;

use App\Validator\Constraints\ExistingMemberConstraint;
use Serializable;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @todo Both the username and the password should be checked. Right now, only
 * the username is checked. This means an attacker is able to know whether
 * whether it is the username or the password that is incorrect.
 */
class UsernameAndPasswordSubmission implements Serializable
{
    /**
     * @Assert\NotBlank()
     * @Assert\Type("string")
     * @ExistingMemberConstraint
     */
    private $username;

    /**
     * @Assert\NotBlank()
     * @Assert\Type("string")
     */
    private $password;

    public function __construct(
        ?string $username = null,
        ?string $password = null)
    {
        $this->username = $username;
        $this->password = $password;
    }

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

    public function serialize(): string
    {
        return serialize([
            $this->username,
            $this->password,
        ]);
    }

    public function unserialize($serialized): void
    {
        list(
            $this->username,
            $this->password) = unserialize($serialized);
    }
}

