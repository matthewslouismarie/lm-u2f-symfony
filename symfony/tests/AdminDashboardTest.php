<?php

namespace App\Tests;

use App\Enum\SecurityStrategy;
use App\Enum\Setting;
use App\Service\AppConfigManager;
use App\Service\Form\Filler\ChallengeSpecificationFiller;
use App\Service\Form\Filler\PasswordConfigFiller;
use App\Service\Form\Filler\U2fConfigFiller;
use App\Service\Form\Filler\UserStudyConfigFiller;
use LM\Common\Enum\Scalar;

class AdminDashboardTest extends TestCaseTemplate
{
    use LoginTrait;

    public function testAdmin()
    {
        $this->doGet('/admin');
        $this->assertEquals(500, $this->getHttpStatusCode());
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
                ->getBoolSetting(Setting::ALLOW_U2F_LOGIN))
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
                ->getSetting(Setting::SECURITY_STRATEGY, Scalar::_STR))
        ;
        $this->submit($button->form([
            'security_strategy[securityStrategyId]' => SecurityStrategy::PWD,
        ]));
        $this->assertEquals(
            SecurityStrategy::PWD,
            $this
                ->getAppConfigManager()
                ->getSetting(Setting::SECURITY_STRATEGY, Scalar::_STR))
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

    public function testChallengeSpecificationPanel()
    {
        $this->logIn();
        $newSettings = [
            Setting::SEC_HIGH_PWD => true,
            Setting::SEC_HIGH_U2F => true,
            Setting::SEC_HIGH_U2F_N => 2,
            Setting::SEC_HIGH_BOTH => true,
            Setting::SEC_MEDM_PWD => false,
            Setting::SEC_MEDM_U2F => true,
            Setting::SEC_MEDM_U2F_N => 1,
            Setting::SEC_MEDM_BOTH => false,
        ];
        $this->doGet('/admin/challenge-specification');
        $this->submit($this
            ->get(ChallengeSpecificationFiller::class)
            ->fillForm($this->getCrawler(), $newSettings))
        ;
        $config = $this->getAppConfigManager();
        foreach ($newSettings as $key => $newSetting) {
            $this->assertSame(
                $config->getSetting($key, gettype($newSetting)),
                $newSetting)
            ;
        }
        $settings2 = [
            Setting::SEC_HIGH_PWD => false,
            Setting::SEC_HIGH_U2F => false,
            Setting::SEC_HIGH_U2F_N => 3,
            Setting::SEC_HIGH_BOTH => false,
            Setting::SEC_MEDM_PWD => true,
            Setting::SEC_MEDM_U2F => false,
            Setting::SEC_MEDM_U2F_N => 0,
            Setting::SEC_MEDM_BOTH => true,
        ];
        $this->submit($this
            ->get(ChallengeSpecificationFiller::class)
            ->fillForm($this->getCrawler(), $settings2))
        ;
        foreach ($settings2 as $key => $setting) {
            $this->assertSame(
                $config->getSetting($key, gettype($setting)),
                $setting)
            ;
        }
    }
}
