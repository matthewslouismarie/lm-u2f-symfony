<?php

namespace App\Tests\Controller;

use App\Service\Form\Filler\LoginRequestFiller;
use App\Service\Form\Filler\CredentialFiller;

class MediumSecurityAuthorizerTest extends TestCaseTemplate
{
    public function testCorrectLogin()
    {
        $this->logIn('louis', 'hello');
        $this->assertIsRedirect();
        $this->followRedirect();
        $this->assertRegExp(
            '/\/all\/u2f-authorization\/medium-security\/[a-z0-9]+/',
            $this->getUri()
        );
        $u2fButton = $this
            ->getCrawler()
            ->selectButton('new_u2f_authentication[submit]')
        ;
        $cycle = $this
            ->getU2fAuthenticationMocker()
            ->getNewCycle()
        ;
        $u2fForm = $u2fButton->form([
            'new_u2f_authentication[u2fTokenResponse]' => $cycle->getResponse(),
        ]);
        $sid = $this->getUriLastPart();
        $this->getSubmissionStack()->set($sid, 2, $cycle->getRequest());
        $this->submit($u2fForm);
        $this->assertIsRedirect();
        $this->followRedirect();
        $this->assertEquals(
            "http://localhost/not-authenticated/finalise-login/{$sid}",
            $this->getUri()
        );
        $loginRequestFiller = $this->get('App\Service\Form\Filler\LoginRequestFiller');
        $this->submit(
            $loginRequestFiller->fillForm($this->getClient()->getCrawler())
        );
    }

    public function testIncorrectUsername()
    {
        $this->logIn('loui', 'hello');
        $this->assertIsNotRedirect();
    }

    public function testIncorrectPassword()
    {
        $this->logIn('louis', '');
        $this->assertIsNotRedirect();
    }

    public function testIncorrectCredentials()
    {
        $this->logIn('', '');
        $this->assertIsNotRedirect();
    }

    private function logIn(string $username, string $password): void
    {
        $this->doGet('/not-authenticated/start-login');
        $this->followRedirect();
        $formFiller = $this->get('App\Service\Form\Filler\CredentialFiller');
        $this->submit(
            $formFiller->fillForm($this->getCrawler(), $password, $username)
        );
    }
}
