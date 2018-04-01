<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @todo Make immutable.
 *
 * @ORM\Entity(repositoryClass="App\Repository\AppSettingRepository")
 */
class AppSetting
{

    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $value;

    public function __construct(string $id, string $value)
    {
        $this->id = $id;
        $this->value = $value;
    }

    public function getId(): string
    {
        return $this->id;
    }
 
    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }
}
