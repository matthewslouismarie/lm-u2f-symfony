<?php

declare(strict_types=1);

namespace App\Tests;

use App\Enum\Setting;
use App\Enum\SecurityStrategy;
use App\DataFixtures\AppFixture;
use App\Service\Form\Filler\CredentialAuthenticationFiller;
use App\Service\Form\Filler\U2fAuthenticationFiller;
use App\Service\Form\Filler\ValidPasswordFiller;
use Exception;
use LM\Common\Enum\Scalar;
use UnexpectedValueException;

trait LoginTrait
{
    public function authenticateAsAdmin(): void
    {
        if ($this->isAuthenticatedFully()) {
            switch ($this->getAppConfigManager()->getSetting(Setting::SECURITY_STRATEGY, Scalar::_STR)) {
                case SecurityStrategy::U2F:
                    $this->submit($this
                        ->get(ValidPasswordFiller::class)
                        ->fillForm($this->getCrawler(), AppFixture::ADMIN_PASSWORD))
                    ;
                    $this->submit($this
                        ->get(U2fAuthenticationFiller::class)
                        ->fillForm($this->getCrawler(), $this->getUriLastPart()))
                    ;
                    break;

                case SecurityStrategy::PWD:
                    $this->submit($this
                        ->get(ValidPasswordFiller::class)
                        ->fillForm($this->getCrawler(), AppFixture::ADMIN_PASSWORD))
                    ;
                    break;

                default:
                    throw new UnexpectedValueException();
            }
        } else {
            throw new Exception('Unsupported yet');
        }
    }

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
            ->getSetting(Setting::ALLOW_PWD_LOGIN, Scalar::_BOOL)
        ;
        $allowU2fLogin = $this
            ->getAppConfigManager()
            ->getSetting(Setting::ALLOW_U2F_LOGIN, Scalar::_BOOL)
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
