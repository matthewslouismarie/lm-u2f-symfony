<?php

namespace App\Tests\Controller\EasyAuthenticationMiddleware;

use App\Tests\TestCaseTemplate;

class LoginTest extends TestCaseTemplate
{
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
            ->fillForm($this->getCrawler(), "louis"))
        ;
        $this->submit($this
            ->get('App\Service\Form\Filler\U2fAuthenticationFiller')
            ->fillForm($this->getCrawler(), $this->getUriLastPart()))
        ;
    }
}
