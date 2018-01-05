<?php

namespace App\FormModel;

use Symfony\Component\Validator\Constraints as Assert;

class UsernameAndPasswordSubmission
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