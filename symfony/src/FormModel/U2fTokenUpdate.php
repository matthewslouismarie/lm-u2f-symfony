<?php

namespace App\FormModel;

use Symfony\Component\Validator\Constraints as Assert;

class U2fTokenUpdate
{
    /**
     * @Assert\NotBlank()
     */
    private $name;

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }
}