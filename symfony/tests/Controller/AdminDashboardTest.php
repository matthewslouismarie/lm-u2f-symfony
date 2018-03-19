<?php

namespace App\Tests\Controller;

use App\Enum\SecurityStrategy;
use App\Enum\Setting;
use App\Service\AppConfigManager;
use App\Tests\TestCaseTemplate;

class AdminDashboardTest extends TestCaseTemplate
{
    use AdminDashboardTrait;

    private function authenticateAsLouis()
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
        $this->assertTrue($this->isAdmin());
    }

    public function testAdmin()
    {
        $this->doGet('/admin');
        $this->assertEquals(302, $this->getHttpStatusCode());
        $this->authenticateAsLouis();
        $this->doGet('/admin');
        $this->assertEquals(200, $this->getHttpStatusCode());        
    }

    public function testAdminOptions()
    {
        $this->authenticateAsLouis();
        $this->doGet('/admin/registration');
        $this->submit($this
            ->get('App\Service\Form\Filler\U2fConfigFiller')
            ->fillForm($this->getCrawler(), true, 2, 3, false)
        );
        $this->assertEquals(
            true,
            $this
                ->getAppConfigManager()
                ->getIntSetting(Setting::ALLOW_U2F_LOGIN))
        ;
        $this->assertEquals(
            2,
            $this
                ->getAppConfigManager()
                ->getIntSetting(Setting::N_U2F_KEYS_POST_AUTH))
        ;
        $this->assertEquals(
            3,
            $this
                ->getAppConfigManager()
                ->getIntSetting(Setting::N_U2F_KEYS_REG))
        ;
        $this->assertEquals(
            false,
            $this
                ->getAppConfigManager()
                ->getBoolSetting(Setting::ALLOW_MEMBER_TO_MANAGE_U2F_KEYS))
        ;
    }

    public function testPassword()
    {
        $this->changePwdSettings([
            'minimumLength' => 4,
            'requireNumbers' => true,
            'requireSpecialCharacters' => true,
            'requireUppercaseLetters' => true,
        ]);
    }

    public function testSecurityMode()
    {
        $this->authenticateAsLouis();
        $this->doGet('/admin/security-strategy');
        $button = $this
            ->getCrawler()
            ->selectButton('security_strategy[submit]')
        ;
        $this->submit($button->form([
            'security_strategy[securityStrategyId]' => SecurityStrategy::U2F,
        ]));
        $this->assertEquals(
            SecurityStrategy::U2F,
            $this
                ->getAppConfigManager()
                ->getIntSetting(Setting::SECURITY_STRATEGY))
        ;
        $this->submit($button->form([
            'security_strategy[securityStrategyId]' => SecurityStrategy::PWD,
        ]));
        $this->assertEquals(
            SecurityStrategy::PWD,
            $this
                ->getAppConfigManager()
                ->getIntSetting(Setting::SECURITY_STRATEGY))
        ;
    }
}
