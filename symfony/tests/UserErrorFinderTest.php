<?php

declare(strict_types=1);

namespace App\Tests;

use App\Service\UserErrorFinder;
use App\Enum\Setting;
use LM\Common\Enum\Scalar;
use App\Enum\SecurityStrategy;

class UserErrorFinderTest extends TestCaseTemplate
{
    public function testU2f()
    {
        $this
            ->getAppConfigManager()
            ->set(Setting::N_U2F_KEYS_LOGIN, 1)
            ->set(Setting::SECURITY_STRATEGY, SecurityStrategy::U2F)
        ;
        $userErrorFinder = $this->get(UserErrorFinder::class);
        $this->assertTrue($userErrorFinder->isError(
            '/not-authenticated/login/u2f/a',
            [
                '/not-authenticated/login/u2f/a',
                '/not-authenticated/login/u2f/a',
                '/not-authenticated/login/u2f/a',
            ]
        ));
        $this->assertFalse($userErrorFinder->isError(
            '/not-authenticated/login/u2f/a',
            [
                '/not-authenticated/login/u2f/a',
            ]
        ));
        $this->assertFalse($userErrorFinder->isError(
            '/not-authenticated/login/u2f/a',
            []
        ));
        $this->assertFalse($userErrorFinder->isError(
            '/',
            []
        ));
        $this->assertFalse($userErrorFinder->isError(
            '/',
            [
                '/not-authenticated/login/u2f/a',
                '/not-authenticated/login/u2f/a',
                '/not-authenticated/login/u2f/a',
            ]
        ));
        $this->assertTrue($userErrorFinder->isError(
            '/not-authenticated/login/u2f/a',
            [
                '/',
                '/not-authenticated/login/u2f/a',
                '/not-authenticated/login/u2f/a',
                '/not-authenticated/login/u2f/a',
            ]
        ));
        $this->assertFalse($userErrorFinder->isError(
            '/not-authenticated/register/u2f-key/a',
            []
        ));
        $this->assertFalse($userErrorFinder->isError(
            '/not-authenticated/register/u2f-key/a',
            [
                '/not-authenticated/register/a',
            ]
        ));
        $this->assertFalse($userErrorFinder->isError(
            '/authenticated/transfer-money/eueeu',
            [
                '/authenticated/transfer-money',
                '/authenticated/transfer-money/eueeu',
            ]
        ));
        $this->assertTrue($userErrorFinder->isError(
            '/authenticated/transfer-money/eueeu',
            [
                '/authenticated/transfer-money',
                '/authenticated/transfer-money/eueeu',
                '/authenticated/transfer-money/eueeu',
                '/authenticated/transfer-money/eueeu',
            ]
        ));
    }

    /**
     * @todo Depend on security strategy.
     */
    public function testPwd()
    {
        $this
            ->getAppConfigManager()
            ->set(Setting::SECURITY_STRATEGY, SecurityStrategy::PWD)
        ;
        $userErrorFinder = $this->get(UserErrorFinder::class);
        $this->assertTrue($userErrorFinder->isError(
            '/not-authenticated/login/pwd/a',
            [
                '/not-authenticated/login/pwd/a',
                '/not-authenticated/login/pwd/a',
            ]
        ));
        $this->assertTrue($userErrorFinder->isError(
            '/not-authenticated/login/pwd/a',
            [
                '/not-authenticated/login/pwd/a',
                '/not-authenticated/login/pwd/a',
            ]
        ));
        $this->assertFalse($userErrorFinder->isError(
            '/not-authenticated/login/pwd/a',
            []
        ));
        $this->assertTrue($userErrorFinder->isError(
            '/not-authenticated/register/a',
            [
                '/not-authenticated/register/a',
                '/not-authenticated/register/a',
            ]
        ));
        $this->assertFalse($userErrorFinder->isError(
            '/not-authenticated/register/a',
            []
        ));
        $this->assertFalse($userErrorFinder->isError(
            '/authenticated/transfer-money',
            []
        ));
        $this->assertFalse($userErrorFinder->isError(
            '/authenticated/transfer-money/eueeu',
            [
                '/authenticated/transfer-money',            
            ]
        ));
        $this->assertTrue($userErrorFinder->isError(
            '/authenticated/transfer-money/eueeu',
            [
                '/authenticated/transfer-money/euieiu',
                '/authenticated/transfer-money/euieiu',
            ]
        ));
        $this->assertSame(
            5,
            $userErrorFinder->getNErrors([
                '/',
                '/not-authenticated/register',
                '/not-authenticated/register/a',
                '/not-authenticated/register/a',
                '/not-authenticated/login/pwd',
                '/not-authenticated/login/pwd/a',
                '/not-authenticated/login/pwd/a',
                '/not-authenticated/login/pwd/a',
                '/not-authenticated/login/pwd/a',
                '/not-authenticated/login/pwd/a',
                '/not-authenticated/login/pwd/a',
                '/authenticated/transfer-money',
                '/authenticated/transfer-money/a',
                '/authenticated/transfer-money/a',
                '/authenticated/transfer-money/a',
            ])
        );
    }
}
