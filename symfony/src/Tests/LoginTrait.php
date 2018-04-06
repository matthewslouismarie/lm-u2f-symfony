<?php

namespace App\Tests;

trait LoginTrait
{
    public function isAuthenticatedFully(): bool
    {
        return $this
            ->get('security.authorization_checker')
            ->isGranted('IS_AUTHENTICATED_FULLY')
        ;
    }
}
