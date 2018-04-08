<?php

namespace App\Tests;

use App\DataFixtures\MembersFixture;
use App\Entity\Member;
use App\Enum\Setting;
use Symfony\Component\HttpFoundation\Request;

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
        $this
            ->getAppConfigManager()
            ->set(Setting::ALLOW_PWD_LOGIN, true)
            ->set(Setting::ALLOW_U2F_LOGIN, false)
        ;
        $this->doGet("/not-authenticated/login/pwd");
        $this->followRedirect();
        $this->submit($this
            ->get("App\Service\Form\Filler\CredentialAuthenticationFiller")
            ->fillForm($this->getCrawler(), "hello", "louis"))
        ;
    }
}
