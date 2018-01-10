<?php

namespace App\FormModel;

use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints\ExistingMemberConstraint;

/**
 * @todo Both the username and the password should be checked. Right now, only
 * the username is checked. This means an attacker is able to know whether
 * whether it is the username or the password that is incorrect.
 */
class UsernameAndPasswordSubmission
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

    public function setUsername($username)
    {
        $this->username = $username;
    }
    
    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getPassword()
    {
        return $this->password;
    }
}