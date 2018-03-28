<?php

namespace App\Tests\Controller;

use App\Tests\SecurityStrategyTrait;
use App\Tests\TestCaseTemplate;

class AuthenticationTest extends TestCaseTemplate
{
    use SecurityStrategyTrait;

    public function testCorrectU2fAuthentication()
    {
        $this->activateU2fSecurityStrategy();
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

    public function testCorrectPwdAuthentication()
    {
        $this->activatePwdSecurityStrategy();
        $this->doGet('/not-authenticated/pwd-authenticate');
        $this->assertIsRedirect();
        $this->followRedirect();
        $this->followRedirect();
        $this->submit(
            $this
            ->get('App\Service\Form\Filler\CredentialAuthenticationFiller')
            ->fillForm($this->getCrawler(), 'hello', 'louis'))
        ;
        $this->followRedirect();

        $this->assertTrue($this->isAuthenticatedFully());
    }

    public function testIncorrectAuthentication()
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
        $this->doGet('/all/initiate-identity-check/'.$this->getUriLastPart());
        $this->assertFalse($this->isRedirect());
    }

    public function testInvalidSid()
    {
        $this->doGet('/all/initiate-identity-check/eutieuieuie');
        $this->assertFalse($this->isRedirect());
        $this->assertContains(
            'error',
            $this->getCrawler()->filterXPath('//body')->text()
        );
        $sid = $this
            ->getSecureSession()
            ->storeString('')
        ;
        $this->doGet("/all/initiate-identity-check/{$sid}");
        $this->assertFalse($this->isRedirect());
        $this->assertContains(
            'error',
            $this->getCrawler()->filterXPath('//body')->text()
        );
    }
}
