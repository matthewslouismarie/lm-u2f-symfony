<?php

namespace App\Service;

use App\Builder\TokenBuilder;
use App\Entity\U2FToken;

class U2FTokenBuilderService
{
    public function createBuilder(U2FToken $base)
    {
        return new U2FTokenBuilder($base);
    }
}