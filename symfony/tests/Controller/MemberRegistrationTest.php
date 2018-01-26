<?php

namespace App\Tests\Controller;

use App\Entity\Member;
use App\Entity\U2fToken;
use App\Service\Form\Filler\CredentialRegistrationFiller;
use App\Service\Form\Filler\CredentialAuthenticationFiller;

class MemberRegistrationTest extends TestCaseTemplate
{
    public function testCorrectRegistration(): void
    {
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

        $filler = $this->get('App\Service\U2fRegistrationFiller');
        $sid = $this->getUriLastPart();
        $form = $filler->fillForm($this->getCrawler(), $sid, 0);
        $this->submit($form);
        $this->assertIsRedirect();
        $this->followRedirect();
        $this->submit($filler->fillForm($this->getCrawler(), $sid, 1));
        $this->assertIsRedirect();
        $this->followRedirect();
        $this->submit($filler->fillForm($this->getCrawler(), $sid, 2));
        $this->assertIsRedirect();
        $this->followRedirect();
        $this->assertEquals(
            'http://localhost/not-authenticated/registration/submit/'.$sid,
            $this->getUri()
        );
        $filler = $this->get('App\Service\Form\Filler\UserConfirmationFiller');
        $this->submit($filler->fillForm($this->getCrawler()));
        $this->assertIsRedirect();
        $this->followRedirect();
        $this->assertEquals(
            'http://localhost/not-authenticated/registration/success',
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
            ->getMemberRegistrations($member->getId())
        ;
        $this->assertEquals(3, count($u2fTokens));
        
        $this->assertFalse(
            $this->get('App\Service\SubmissionStack')->isValidSid($sid)
        );
    }

    public function testResetButton(): void
    {
        $stack = $this->get('App\Service\SubmissionStack');
        $this->doGet('/not-authenticated/registration/start');
        $this->followRedirect();
        $filler = $this->get('App\Service\Form\Filler\CredentialRegistrationFiller');
        $this->submit(
            $filler->fillForm($this->getCrawler(), 'pwd', 'pwd', 'chat')
        );

        $this->followRedirect();
        $sid = $this->getUriLastPart();

        $filler = $this->get('App\Service\U2fRegistrationFiller');
        $form = $filler->fillForm($this->getCrawler(), $sid, 0);
        $this->submit($form);

        $this->doGet('/not-authenticated/registration/reset/'.$sid);
        $userConfirmationFiller = $this
            ->get('App\Service\Form\Filler\UserConfirmationFiller')
        ;
        $this->submit($userConfirmationFiller->fillForm($this->getCrawler()));
        $this->assertFalse(
            $this->get('App\Service\SubmissionStack')->isValidSid($sid)
        );

        $this->assertContains('successfully reset.', $this->getCrawler()->text());
    }
}
