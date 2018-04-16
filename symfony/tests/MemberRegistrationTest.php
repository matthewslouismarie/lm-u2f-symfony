<?php

namespace App\Tests;

use App\Entity\Member;
use App\Entity\U2fToken;
use App\Enum\Setting;

class MemberRegistrationTest extends TestCaseTemplate
{
    use SecurityStrategyTrait;

    public function testCorrectRegistrationU2f(): void
    {
        $this->activateU2fSecurityStrategy();

        $this->doGet('/not-authenticated/registration/start');
        $this->assertIsRedirect();
        $this->followRedirect();
        $this->assertEquals(200, $this->getHttpStatusCode());
        $filler = $this->get('App\Service\Form\Filler\CredentialRegistrationFiller');
        $this->submit(
            $filler->fillForm($this->getCrawler(), 'pwd', 'pwd', 'chat')
        );

        $this->assertIsRedirect();
        $this->followRedirect();

        $filler = $this->get('App\Service\Form\Filler\U2fRegistrationFiller');
        $sid = $this->getUriLastPart();
        $form = $filler->fillForm($this->getCrawler(), $sid, 0);
        $this->submit($form);
        $this->assertIsRedirect();
        $this->followRedirect();
        $this->assertEquals(
            'http://localhost/not-authenticated/registration/submit/'.$sid,
            $this->getUri()
        );
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
        )
        ;

        foreach ($u2fTokens as $u2fToken) {
            $this->assertEquals('a random name', $u2fToken->getU2fKeyName());
        }
    }

    public function testCorrectRegistrationPwd(): void
    {
        $this->activatePwdSecurityStrategy();
        
        $this->doGet('/not-authenticated/registration/start');
        $this->assertIsRedirect();
        $this->followRedirect();
        $this->assertEquals(200, $this->getHttpStatusCode());
        $filler = $this->get('App\Service\Form\Filler\CredentialRegistrationFiller');
        $this->submit(
            $filler->fillForm($this->getCrawler(), 'JeeeSuis_58', 'JeeeSuis_58', 'chat')
        );

        $this->followRedirect();
        $this->followRedirect();

        $member = $this
            ->getObjectManager()
            ->getRepository(Member::class)
            ->getMember('chat')
        ;
        $this->assertNotNull($member);
    }

    public function testResetButton(): void
    {
        $this->activateU2fSecurityStrategy();
        
        $this->doGet('/not-authenticated/registration/start');
        $this->followRedirect();
        $filler = $this->get('App\Service\Form\Filler\CredentialRegistrationFiller');
        $this->submit(
            $filler->fillForm($this->getCrawler(), 'pwd', 'pwd', 'chat')
        );

        $this->followRedirect();
        $sid = $this->getUriLastPart();

        $filler = $this->get('App\Service\Form\Filler\U2fRegistrationFiller');
        $form = $filler->fillForm($this->getCrawler(), $sid, 0);
        $this->submit($form);

        $this->doGet('/not-authenticated/registration/reset/'.$sid);
        $userConfirmationFiller = $this
            ->get('App\Service\Form\Filler\UserConfirmationFiller')
        ;
        $this->submit($userConfirmationFiller->fillForm($this->getCrawler()));

        $this->assertContains('successfully reset.', $this->getCrawler()->text());
    }
}
