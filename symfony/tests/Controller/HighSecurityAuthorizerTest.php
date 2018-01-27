<?php

namespace App\Tests\Controller;

class HighSecurityAuthorizerTest extends TestCaseTemplate
{
    public function test()
    {
        $this->doGet('/not-authenticated/request-password-reset');
        $this->assertIsRedirect();
        $this->followRedirect();
        $this->submit(
            $this
                ->get('App\Service\Form\Filler\ExistingUsernameFiller')
                ->fillForm($this->getCrawler(), 'louis')
        );
        $this->assertIsRedirect();
        $this->followRedirect();
        $sid = $this->getUriLastPart();
        $this->submit(
            $this
                ->get('App\Service\Form\Filler\U2fAuthenticationFiller')
                ->fillForm($this->getCrawler(), $sid, 0)
        );
        $this->assertIsRedirect();
        $this->followRedirect();
        $this->submit(
            $this
                ->get('App\Service\Form\Filler\U2fAuthenticationFiller')
                ->fillForm($this->getCrawler(), $sid, 1)
        );
        $this->assertIsRedirect();
    }
}
