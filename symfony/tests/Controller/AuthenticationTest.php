<?php

namespace App\Tests\Controller;

use App\Tests\TestCaseTemplate;

class AuthenticationTest extends TestCaseTemplate
{
    public function testCorrectAuthentication()
    {
        $this->doGet('/not-authenticated/authenticate');
        $this->assertIsRedirect();
        $this->followRedirect();
        $this->followRedirect();
        $this->submit(
            $this
            ->get('App\Service\Form\Filler\ExistingUsernameFiller')
            ->fillForm($this->getCrawler(), 'louis'))
        ;
        $this->followRedirect();

        $this->submit(
            $this
            ->get('App\Service\Form\Filler\U2fAuthenticationFiller1')
            ->fillForm($this->getCrawler(), $this->getUriLastPart()))
        ;

        $this->followRedirect();
        $this->assertTrue($this->isAuthenticatedFully());
    }
}
