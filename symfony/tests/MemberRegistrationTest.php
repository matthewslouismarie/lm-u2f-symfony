<?php

namespace App\Tests;

use App\Entity\Member;
use App\Entity\U2fToken;
use App\Enum\Setting;
use App\Service\Form\Filler\CredentialRegistrationFiller;
use App\Service\Form\Filler\U2fRegistrationFiller;
use App\Service\Form\Filler\UserConfirmationFiller;

class MemberRegistrationTest extends TestCaseTemplate
{
    use SecurityStrategyTrait;

    public function testCorrectRegistrationU2f(): void
    {
        $this->activateU2fSecurityStrategy();

        $this->doGet('/not-authenticated/registration');
        $this->assertIsRedirect();
        $this->followRedirect();
        $this->assertEquals(200, $this->getHttpStatusCode());
        $filler = $this->get(CredentialRegistrationFiller::class);
        $this->submit(
            $filler->fillForm($this->getCrawler(), 'pwd', 'pwd', 'chat')
        );

        $filler = $this->get(U2fRegistrationFiller::class);
        $sid = $this->getUriLastPart();
        $form = $filler->fillForm($this->getCrawler(), $sid);
        $this->submit($form);
        $member = $this
            ->getObjectManager()
            ->getRepository(Member::class)
            ->getMember('chat')
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
