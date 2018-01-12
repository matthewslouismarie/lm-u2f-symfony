<?php

namespace App\Service;

use App\Builder\U2fTokenBuilder;
use App\Entity\U2fToken;

class U2fTokenBuilderService
{
    public function createBuilder(U2fToken $base)
    {
        return new U2fTokenBuilder($base);
    }
}
