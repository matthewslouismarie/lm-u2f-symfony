<?php

namespace App\Tests\Controller;

use App\Enum\Setting;

trait AdminDashboardTrait
{
    private function changePwdSettings(array $pwdSettings)
    {
        $config = $this->getAppConfigManager();
        $this->authenticateAsLouis();
        $this->doGet('/admin/password');
        $this->submit($this
            ->get('App\Service\Form\Filler\PasswordConfigFiller')
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
