<?php

namespace App\Tests;

use App\Enum\SecurityStrategy;
use App\Enum\Setting;
use App\Service\AppConfigManager;
use App\Service\Form\Filler\PasswordConfigFiller;
use App\Service\Form\Filler\U2fConfigFiller;
use App\Service\Form\Filler\UserStudyConfigFiller;

class AdminDashboardTest extends TestCaseTemplate
{
    use LoginTrait;

    public function testAdmin()
    {
        $this->doGet('/admin');
        $this->assertEquals(302, $this->getHttpStatusCode());
        $this->login();
        $this->doGet('/admin');
        $this->assertEquals(200, $this->getHttpStatusCode());        
    }

    public function testAdminOptions()
    {
        $this->login();
        $this->doGet('/admin/registration');
        $this->submit($this
            ->get(U2fConfigFiller::class)
            ->fillForm($this->getCrawler(), true, 2, 3, false))
        ;
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
            'enforceMinLength' => true,
            'requireNumbers' => true,
            'requireSpecialCharacters' => true,
            'requireUppercaseLetters' => true,
        ]);
    }

    public function testSecurityMode()
    {
        $this->login();
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

    public function testUserStudy()
    {
        $this->login();
        $this->doGet('/admin/user-study');
        $this->submit($this
            ->get(UserStudyConfigFiller::class)
            ->fillForm($this->getCrawler(), true, 'P0'))
        ;
        $this->assertEquals(
            true,
            $this
                ->getAppConfigManager()
                ->getBoolSetting(Setting::USER_STUDY_MODE_ACTIVE))
        ;
        $this->assertEquals(
            "P0",
            $this
                ->getAppConfigManager()
                ->getStringSetting(Setting::PARTICIPANT_ID))
        ;
        $this->submit($this
            ->get(UserStudyConfigFiller::class)
            ->fillForm($this->getCrawler(), true, null))
        ;
        $this->assertEquals(
            "P0",
            $this
                ->getAppConfigManager()
                ->getStringSetting(Setting::PARTICIPANT_ID))
        ;
        $this->assertContains(
            "You must provide",
            $this
                ->getClient()
                ->getResponse()
                ->getContent())
        ;
    }


    private function changePwdSettings(array $pwdSettings)
    {
        $config = $this->getAppConfigManager();
        $this->login();
        $this->doGet('/admin/password');
        $this->submit($this
            ->get(PasswordConfigFiller::class)
            ->fillForm(
                $this->getCrawler(),
                $pwdSettings['minimumLength'],
                $pwdSettings['enforceMinLength'],
                $pwdSettings['requireNumbers'],
                $pwdSettings['requireSpecialCharacters'],
                $pwdSettings['requireUppercaseLetters']))
        ;

        $this->assertEquals(
            $pwdSettings['enforceMinLength'],
            $this
                ->getAppConfigManager()
                ->getBoolSetting(Setting::PWD_ENFORCE_MIN_LENGTH))
                ;

        $this->assertEquals(
            $pwdSettings['minimumLength'],
            $this
                ->getAppConfigManager()
                ->getIntSetting(Setting::PWD_MIN_LENGTH))
        ;

        $this->assertEquals(
            $pwdSettings['requireNumbers'],
            $config->getBoolSetting(Setting::PWD_NUMBERS))
        ;

        $this->assertEquals(
            $pwdSettings['requireSpecialCharacters'],
            $config->getBoolSetting(Setting::PWD_SPECIAL_CHARS))
        ;

        $this->assertEquals(
            $pwdSettings['requireUppercaseLetters'],
            $config->getBoolSetting(Setting::PWD_UPPERCASE))
        ;
    }
}
