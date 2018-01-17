<?php

namespace App\Tests;

class TestCaseTemplate extends DbWebTestCase
{
    public function isRedirect(): bool
    {
        return $this->getClient()->isRedirect();
    }
}