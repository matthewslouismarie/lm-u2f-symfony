<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\TestCaseTemplate;
use App\Service\Form\Filler\CredentialRegistrationFiller;
use App\Service\AppConfigManager;
use App\Enum\Setting;
use App\Entity\Member;
use App\Entity\U2fToken;
use App\Service\Form\Filler\U2fRegistrationFiller;

class AccountCreationTest extends TestCaseTemplate
{
    const USER_ID = 'user_id';

    const USER_PWD = 'user_pwd';

    public function testNoU2fDevices()
    {
        $this->createAccount(0);
    }

    public function testOneU2fDevices()
    {
        $this->createAccount(1);
    }

    public function testTwoU2fDevices()
    {
        $this->createAccount(2);
    }

    private function createAccount(int $nU2fDevices)
    {
        $memberRepo = $this
            ->getObjectManager()
            ->getRepository(Member::class)
        ;
        $this->assertNull($memberRepo->findOneBy([
            'username' => self::USER_ID,
        ]));
        $this
            ->get(AppConfigManager::class)
            ->set(Setting::N_U2F_KEYS_REG, $nU2fDevices)
            ->set(Setting::PWD_NUMBERS, false)
            ->set(Setting::PWD_SPECIAL_CHARS, false)
            ->set(Setting::PWD_UPPERCASE, false)
            ->set(Setting::PWD_ENFORCE_MIN_LENGTH, false)
        ;
        $this->doGet('/not-authenticated/account-creation');
        $this->assertSame(302, $this->getHttpStatusCode());
        $this->followRedirect();
        $this->assertSame(200, $this->getHttpStatusCode());
        $this->submit(
            $this
            ->get(CredentialRegistrationFiller::class)
            ->fillForm(
                $this->getCrawler(),
                self::USER_PWD,
                self::USER_PWD,
                self::USER_ID
            )
        );
        for ($i = 0; $i < $nU2fDevices; ++$i) {
            $this->submit(
                $this
                ->get(U2fRegistrationFiller::class)
                ->fillForm($this->getCrawler(), $this->getUriLastPart())
            );
        }
        $this->assertSame(200, $this->getHttpStatusCode());
        $member = $memberRepo->findOneBy([
            'username' => self::USER_ID,
        ]);
        $this->assertInstanceOf(Member::class, $member);
        $this->assertTrue(password_verify(
            self::USER_PWD,
            $member->getHashedPassword()
        ));
        $u2fTokens = $this
            ->getObjectManager()
            ->getRepository(U2fToken::class)
            ->getMemberRegistrations($member)
        ;
        $this->assertSame($nU2fDevices, count($u2fTokens));
    }
}
