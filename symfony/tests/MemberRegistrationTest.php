<?php

declare(strict_types=1);

namespace App\Tests;

use App\Entity\Member;
use App\Entity\U2fToken;
use App\Enum\Setting;
use App\Service\Form\Filler\CredentialRegistrationFiller;
use App\Service\Form\Filler\U2fRegistrationFiller;

class MemberRegistrationTest extends TestCaseTemplate
{
    use SecurityStrategyTrait;

    public function testRegistration()
    {
        $this->register(2, 'user2');
        $this->register(1, 'user1');
    }

    private function register(int $nU2fDevices, string $username): void
    {
        $this->activateU2fSecurityStrategy($nU2fDevices);

        $this->doGet('/not-authenticated/registration');
        $this->assertIsRedirect();
        $this->followRedirect();
        $this->assertEquals(200, $this->getHttpStatusCode());
        $filler = $this->get(CredentialRegistrationFiller::class);
        $this->submit(
            $filler->fillForm($this->getCrawler(), 'pwd', 'pwd', $username)
        );

        $filler = $this->get(U2fRegistrationFiller::class);
        for ($i = 0; $i < $nU2fDevices; $i++) {
            $form = $filler->fillForm(
                $this->getCrawler(),
                $this->getUriLastPart()
            );
            $this->submit($form);
        }
        $member = $this
            ->getObjectManager()
            ->getRepository(Member::class)
            ->getMember($username)
        ;
        $this->assertNotNull($member);
        $u2fTokens = $this
            ->getObjectManager()
            ->getRepository(U2fToken::class)
            ->findBy(['member' => $member])
        ;
        $this->assertEquals(
            $this
                ->getAppConfigManager()
                ->getIntSetting(Setting::N_U2F_KEYS_REG),
            count($u2fTokens)
        );
    }
}
