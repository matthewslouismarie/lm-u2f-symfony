<?php

namespace App\Tests\Controller;

class UpukTest extends AbstractUpukTestCase
{
    public function testUpukFirewall()
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
            '/tks-upuk/not-authenticated/authenticate/username-and-password',
            200)
        ;
        $this->checkUrlStatusCode(
            '/tks-upuk/not-authenticated/authenticate/u2f-key',
            302)
        ;
        $this->checkUrlStatusCode(
            '/tks-upuk/authenticated/change-password',
            302)
        ;
        $this->checkUrlStatusCode(
            '/tks-upuk/authenticated/log-out',
            302)
        ;
    }

    public function runLoggedInTests()
    {
        $this->checkUrlStatusCode(
            '/tks-upuk/not-authenticated/authenticate/username-and-password',
            302)
        ;
        $this->checkUrlStatusCode(
            '/tks-upuk/authenticated/change-password',
            200)
        ;
        $this->checkUrlStatusCode(
            '/tks-upuk/authenticated/log-out',
            200)
        ;
    }
}