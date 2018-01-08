<?php

namespace App\Tests\Controller;

use App\Tests\DbWebTestCase;

class UpukTest extends DbWebTestCase
{
    public function testUpukFirewall()
    {
        $this->runLoggedOutTests();
    }

    public function runLoggedOutTests()
    {
        $this->checkUrlStatusCode('/tks-upuk/not-authenticated/authenticate', 200);
    }
}