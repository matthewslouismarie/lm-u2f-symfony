<?php

declare(strict_types=1);

namespace App\Tests;

use App\Service\UserErrorFinder;
use App\Enum\Setting;
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
            '/not-authenticated/account-creation/a',
            []
        ));
        $this->assertFalse($userErrorFinder->isError(
            '/not-authenticated/account-creation/a',
            [
                '/not-authenticated/account-creation/a',
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
        $this->assertFalse($userErrorFinder->isError(
            '/not-authenticated/account-creation/a',
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
    }

    public function testAccountCreationNoU2fDevices()
    {
        $userErrorFinder = $this->get(UserErrorFinder::class);
        $this
            ->getAppConfigManager()
            ->set(Setting::N_U2F_KEYS_REG, 0)
        ;
        $this->assertFalse($userErrorFinder->isError(
            '/not-authenticated/account-creation/a',
            [
            ]
        ));
        $this->assertFalse($userErrorFinder->isError(
            '/not-authenticated/account-creation/a',
            [
                '/not-authenticated/account-creation/a',
            ]
        ));
        $this->assertTrue($userErrorFinder->isError(
            '/not-authenticated/account-creation/a',
            [
                '/not-authenticated/account-creation/a',
                '/not-authenticated/account-creation/a',
            ]
        ));
    }

    public function testAccountCreationOneU2fDevice()
    {
        $userErrorFinder = $this->get(UserErrorFinder::class);
        $this
            ->getAppConfigManager()
            ->set(Setting::N_U2F_KEYS_REG, 1)
        ;
        $this->assertFalse($userErrorFinder->isError(
            '/not-authenticated/account-creation/a',
            [
                '/not-authenticated/account-creation/a',
                '/not-authenticated/account-creation/a',
            ]
        ));
        $this->assertTrue($userErrorFinder->isError(
            '/not-authenticated/account-creation/a',
            [
                '/not-authenticated/account-creation/a',
                '/not-authenticated/account-creation/a',
                '/not-authenticated/account-creation/a',
            ]
        ));
    }

    public function testAccountCreationTwoU2fDevices()
    {
        $userErrorFinder = $this->get(UserErrorFinder::class);
        $this
            ->getAppConfigManager()
            ->set(Setting::N_U2F_KEYS_REG, 2)
        ;
        $this->assertFalse($userErrorFinder->isError(
            '/not-authenticated/account-creation/a',
            [
                '/not-authenticated/account-creation/a',
                '/not-authenticated/account-creation/a',
                '/not-authenticated/account-creation/a',
            ]
        ));
        $this->assertTrue($userErrorFinder->isError(
            '/not-authenticated/account-creation/a',
            [
                '/not-authenticated/account-creation/a',
                '/not-authenticated/account-creation/a',
                '/not-authenticated/account-creation/a',
                '/not-authenticated/account-creation/a',
            ]
        ));
    }

    public function testList()
    {
        $userErrorFinder = $this->get(UserErrorFinder::class);
        $this
            ->getAppConfigManager()
            ->set(Setting::ALLOW_PWD_LOGIN, true)
            ->set(Setting::SECURITY_STRATEGY, SecurityStrategy::PWD)
        ;
        $this->assertSame(
            5,
            $userErrorFinder->getNErrors([
                '/',
                '/not-authenticated/account-creation',
                '/not-authenticated/account-creation/a',
                '/not-authenticated/account-creation/a',
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

    public function testListPwd()
    {
        $userErrorFinder = $this->get(UserErrorFinder::class);
        $this
            ->getAppConfigManager()
            ->set(Setting::ALLOW_PWD_LOGIN, true)
        ;
        $this->assertSame(
            2,
            $userErrorFinder->getNErrors([
                '/not-authenticated/login/pwd/34b2dd9180db75457e865e4ef14e4364',
                '/not-authenticated/login/pwd/34b2dd9180db75457e865e4ef14e4364',
                '/not-authenticated/login/pwd/34b2dd9180db75457e865e4ef14e4364',
                '/not-authenticated/login/pwd/34b2dd9180db75457e865e4ef14e4364',
            ])
        );
    }
}
