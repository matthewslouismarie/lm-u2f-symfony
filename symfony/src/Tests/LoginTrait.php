<?php

namespace App\Tests;

use App\Enum\Setting;
use App\DataFixtures\AppFixture;
use App\Service\Form\Filler\CredentialAuthenticationFiller;
use App\Service\Form\Filler\U2fAuthenticationFiller;

trait LoginTrait
{
    public function isAuthenticatedFully(): bool
    {
        return $this
            ->get('security.authorization_checker')
            ->isGranted('IS_AUTHENTICATED_FULLY')
        ;
    }

    public function login()
    {
        $allowPwdLogin = $this
            ->getAppConfigManager()
            ->getBoolSetting(Setting::ALLOW_PWD_LOGIN)
        ;
        $allowU2fLogin = $this
            ->getAppConfigManager()
            ->getBoolSetting(Setting::ALLOW_U2F_LOGIN, true)
        ;
        if ($allowPwdLogin) {
            $this->doGet("/not-authenticated/login/pwd");
            $this->followRedirect();
            $this->submit($this
                ->get(CredentialAuthenticationFiller::class)
                ->fillForm($this->getCrawler(), AppFixture::ADMIN_PASSWORD, AppFixture::ADMIN_USERNAME))
            ;
        } elseif ($allowU2fLogin) {
            $this->doGet("/not-authenticated/login/u2f");
            $this->followRedirect();
            $this->submit($this
                ->get(U2fAuthenticationFiller::class)
                ->fillForm($this->getCrawler()))
            ;
        } else {
            $this->fail('Can\'t log in if neither U2F login nor PWD login is allowed');
        }
    }

    public function performHighSecurityIdCheck()
    {
        $this->followRedirect();
        $this->submit($this
            ->get(CredentialAuthenticationFiller::class)
            ->fillForm($this->getCrawler(), AppFixture::ADMIN_PASSWORD, AppFixture::ADMIN_USERNAME))
        ;
    }
}
