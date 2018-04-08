<?php

namespace App\Tests;

use App\Enum\Setting;
use App\Service\Form\Filler\CredentialAuthenticationFiller;

trait LoginTrait
{
    public function isAuthenticatedFully(): bool
    {
        return $this
            ->get('security.authorization_checker')
            ->isGranted('IS_AUTHENTICATED_FULLY')
        ;
    }

    /**
     * @todo Shouldn't change settings.
     */
    public function login()
    {
        $this
            ->getAppConfigManager()
            ->set(Setting::ALLOW_PWD_LOGIN, true)
            ->set(Setting::ALLOW_U2F_LOGIN, false)
        ;
        $this->doGet("/not-authenticated/login/pwd");
        $this->followRedirect();
        $this->submit($this
            ->get(CredentialAuthenticationFiller::class)
            ->fillForm($this->getCrawler(), "hello", "louis"))
        ;
    }

    public function performHighSecurityIdCheck()
    {
        $this->followRedirect();
        $this->submit($this
            ->get(CredentialAuthenticationFiller::class)
            ->fillForm($this->getCrawler(), "hello", "louis"))
        ;
    }
}
