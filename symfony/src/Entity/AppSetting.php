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

    public function __construct(string $id, $unserializedValue)
    {
        $this->id = $id;
        $this->value = serialize($unserializedValue);
    }

    public function getId(): string
    {
        return $this->id;
    }
 
    public function getValue()
    {
        return unserialize($this->value);
    }

    public function setValue($unserializedValue): void
    {
        $this->value = serialize($unserializedValue);
    }
}
