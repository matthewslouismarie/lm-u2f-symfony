<?php

namespace App\Tests;

use App\DataFixtures\MembersFixture;
use App\Enum\Setting;
use App\Tests\TestCaseTemplate;
use App\Tests\LoginTrait;
use LM\Authentifier\Model\AuthenticationProcess;
use LM\Authentifier\Model\RequestDatum;
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
            ->set(Setting::N_U2F_KEYS_LOGIN, 1)
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
        $this->assertTrue($this->isAuthenticatedFully());
    }

    public function testLoginWithTwoU2fKeys()
    {
        $this
            ->getAppConfigManager()
            ->set(Setting::ALLOW_U2F_LOGIN, true)
            ->set(Setting::ALLOW_PWD_LOGIN, false)
            ->set(Setting::N_U2F_KEYS_LOGIN, 2)
        ;
        $this->doGet("/not-authenticated/login");
        $this->followRedirect();
        $this->submit($this
            ->get("App\Service\Form\Filler\ExistingUsernameFiller")
            ->fillForm($this->getCrawler(), "louis"))
        ;
        $this->assertSame(
            0,
            $this
                ->getSecureSession()
                ->getObject($this->getUriLastPart(), AuthenticationProcess::class)
                ->getDataManager()
                ->get(RequestDatum::KEY_PROPERTY, "used_u2f_key_public_keys")
                ->getOnlyValue()
                ->get(RequestDatum::VALUE_PROPERTY, ArrayObject::class)
                ->getSize())
        ;
        $this->submit($this
            ->get('App\Service\Form\Filler\U2fAuthenticationFiller')
            ->fillForm($this->getCrawler(), $this->getUriLastPart()))
        ;
        $this->assertSame(
            1,
            $this
                ->getSecureSession()
                ->getObject($this->getUriLastPart(), AuthenticationProcess::class)
                ->getDataManager()
                ->get(RequestDatum::KEY_PROPERTY, "used_u2f_key_public_keys")
                ->getOnlyValue()
                ->get(RequestDatum::VALUE_PROPERTY, ArrayObject::class)
                ->getSize())
        ;
        $this->submit($this
            ->get('App\Service\Form\Filler\U2fAuthenticationFiller')
            ->fillForm($this->getCrawler(), $this->getUriLastPart()))
        ;
        $this->assertContains('The U2F key is not recognised.', $this
            ->getClient()
            ->getResponse()
            ->getContent())
        ;
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
        $this->assertTrue($this->isAuthenticatedFully());
    }
}
