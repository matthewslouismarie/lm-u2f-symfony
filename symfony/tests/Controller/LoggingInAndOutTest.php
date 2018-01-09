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

    public function runLoggedOutTests()
    {
        $this->checkUrlStatusCode(
            '/not-authenticated/authenticate/username-and-password',
            200)
        ;
        $this->checkUrlStatusCode(
            '/not-authenticated/authenticate/u2f-key',
            302)
        ;
        $this->checkUrlStatusCode(
            '/authenticated/change-password',
            302)
        ;
        $this->checkUrlStatusCode(
            '/authenticated/log-out',
            302)
        ;
    }

    public function runLoggedInTests()
    {
        $this->checkUrlStatusCode(
            '/not-authenticated/authenticate/username-and-password',
            302)
        ;
        $this->checkUrlStatusCode(
            '/authenticated/change-password',
            200)
        ;
        $this->checkUrlStatusCode(
            '/authenticated/log-out',
            200)
        ;
    }
}