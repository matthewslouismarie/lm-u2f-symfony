<?php

namespace App\Service;

use App\Factory\U2fTokenFactory;
use App\Entity\U2fToken;

class U2fTokenFactoryService
{
    public function createBuilder(U2fToken $base)
    {
        return new U2fTokenFactory($base);
    }
}
