<?php

namespace App\Tests\Controller;

use App\Form\Filler\CredentialRegistrationFiller;

class MemberRegistrationTest extends TestCaseTemplate
{
    public function testRegistration(): void
    {
        $this->doGet('/not-authenticated/register');
        $this->assertIsRedirect();
        $this->followRedirect();
        $this->assertEquals(200, $this->getHttpStatusCode());
        $filler = new CredentialRegistrationFiller(
            $this->getCrawler(),
            'pwd',
            'pwd',
            'chat')
        ;
        $this->submit($filler->fillForm());

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
            'http://localhost/not-authenticated/registration/submit',
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
        $this->doGet('/not-authenticated/start-login');
        $this->followRedirect();
        $filler = new CredentialFiller(
            $this->getCrawler(),
            'louis',
            'hello'
        );
        $this->submit($filler->getFilledForm());
        $this->followRedirect();

        /**
         * @todo Move in a new filler class.
         */
        $u2fAuthButton = $this
            ->getCrawler()
            ->selectButton('new_u2f_authentication[submit]')
        ;
        $cycle = $this
            ->getU2fAuthenticationMocker()
            ->getNewCycle()
        ;
        $sid = $this->getUriLastPart();
        $this->getSubmissionStack()->set($sid, 2, $cycle->getRequest());
        $u2fAuthForm = $u2fAuthButton->form([
            'new_u2f_authentication[u2fTokenResponse]' => $cycle->getResponse(),
        ]);
        
        $this->submit($u2fAuthForm);
        $this->followRedirect();
        $btn = $this->getCrawler()->selectButton('login_request[submit]');
        $this->submit($btn->form());

        $this->doGet('/not-authenticated/start-login');
        $this->followRedirect();        
        $this->assertEquals(
            'http://localhost/authenticated/not-logged-out',
            $this->getUri()
        );
    }
}
