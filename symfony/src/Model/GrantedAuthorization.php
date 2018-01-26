<?php

namespace App\Model;

use Serializable;

class GrantedAuthorization implements Serializable
{
    public function serialize(): string
    {
        return serialize([]);
    }

    public function unserialize($serialized): void
    {
        unserialize($serialized);
    }
}