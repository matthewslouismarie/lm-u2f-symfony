<?php

namespace App\FormModel;

use Symfony\Component\Validator\Constraints as Assert;

class U2fTokenUpdate
{
    /**
     * @todo check NotBlank() is the same as NotNull()
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