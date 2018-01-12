<?php

namespace App\Tests\Controller;

class LoggingInAndOutTest extends AbstractAccessManagementTestCase
{
    public function testLoggingInAndOut()
    {
        $this->runLoggedOutTests();
        $this->logIn('louis', 'hello');
        $this->runLoggedInTests();
        $this->logOut();
        $this->runLoggedOutTests();
    }
}
