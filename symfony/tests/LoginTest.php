<?php

namespace App\Tests;

use App\Enum\Setting;
use App\Tests\TestCaseTemplate;
use App\Tests\LoginTrait;
use LM\Common\Model\BooleanObject;

class LoginTest extends TestCaseTemplate
{
    use LoginTrait;

    /**
     * @todo Test with incorrect username, U2F responses, CSRF tokens.
     */
    public function testLogin()
    {
        $this
            ->getAppConfigManager()
            ->set(Setting::ALLOW_U2F_LOGIN, true)
            ->set(Setting::ALLOW_PWD_LOGIN, false)
        ;
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
        $this->assertNotContains(
            'This form should not contain extra fields.',
            $this->getClient()->getResponse()->getContent())
        ;
        $this->submit($this
            ->get('App\Service\Form\Filler\U2fAuthenticationFiller')
            ->fillForm($this->getCrawler(), $this->getUriLastPart()))
        ;
        $this->followRedirect();
        $this->assertTrue($this->isAuthenticatedFully());
    }

    public function testLoginWithTwoU2fKeys()
    {
        $nU2fKeysLogin = 2;
        $this
            ->getAppConfigManager()
            ->set(Setting::ALLOW_U2F_LOGIN, true)
            ->set(Setting::ALLOW_PWD_LOGIN, false)
            ->set(Setting::N_U2F_KEYS_LOGIN, $nU2fKeysLogin)
        ;
        $this->doGet("/not-authenticated/login");
        $this->followRedirect();
        $this->submit($this
            ->get("App\Service\Form\Filler\ExistingUsernameFiller")
            ->fillForm($this->getCrawler(), "louis"))
        ;
        for ($i = 0; $i < $nU2fKeysLogin; $i++) {
            $this->submit($this
                ->get('App\Service\Form\Filler\U2fAuthenticationFiller')
                ->fillForm($this->getCrawler(), $this->getUriLastPart()))
            ;
        }
        $this->followRedirect();
        $this->assertTrue($this->isAuthenticatedFully());
    }

    public function testCredentialLogin()
    {
        $this
            ->getAppConfigManager()
            ->set(Setting::ALLOW_PWD_LOGIN, true)
            ->set(Setting::ALLOW_U2F_LOGIN, false)
        ;
        $this->doGet("/not-authenticated/login");
        $this->assertIsRedirect();
        $this->followRedirect();
        $this->submit($this
            ->get("App\Service\Form\Filler\CredentialAuthenticationFiller")
            ->fillForm($this->getCrawler(), "hello", "louis"))
        ;
        $this->followRedirect();
        $this->assertTrue($this->isAuthenticatedFully());
    }
}
