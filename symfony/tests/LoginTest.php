<?php

namespace App\Tests;

use App\Tests\TestCaseTemplate;
use App\Tests\LoginTrait;

class LoginTest extends TestCaseTemplate
{
    use LoginTrait;

    /**
     * @todo Test with incorrect username, U2F responses, CSRF tokens.
     */
    public function testLogin()
    {
        $this->doGet("/not-authenticated/login");
        $this->assertIsRedirect();
        $this->followRedirect();
        $this->submit($this
            ->get("App\Service\Form\Filler\ExistingUsernameFiller")
            ->fillForm($this->getCrawler(), "lous"))
        ;
        $this->submit($this
            ->get("App\Service\Form\Filler\ExistingUsernameFiller")
            ->fillForm($this->getCrawler(), "louis"))
        ;
        $this->submit($this
            ->get('App\Service\Form\Filler\U2fAuthenticationFiller')
            ->fillForm($this->getCrawler(), $this->getUriLastPart()))
        ;
        $this->followRedirect();
        $this->assertTrue($this->isAuthenticatedFully());
    }
}
