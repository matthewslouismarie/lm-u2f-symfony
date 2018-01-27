<?php

namespace App\Model;

use Serializable;

/**
 * @todo Delete.
 */
class Integer implements Serializable
{
    private $integer;

    public function __construct(int $integer)
    {
        $this->integer = $integer;
    }

    public function getInteger(): int
    {
        return $this->integer;
    }

    public function serialize(): string
    {
        return $this->integer;
    }

    public function unserialize($serialized): void
    {
        $this->integer = $serialized;
    }
}
