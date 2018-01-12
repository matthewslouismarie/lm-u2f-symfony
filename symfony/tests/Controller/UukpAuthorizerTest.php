<?php

namespace App\Tests\Controller;

use App\Model\AuthorizationRequest;
use App\Entity\Member;
use App\Entity\U2FToken;

class UukpAuthorizerTest extends AbstractAccessManagementTestCase
{
    private $sSession;

    public function setUp()
    {
        parent::setUp();
        $this->sSession = $this
            ->getContainer()
            ->get('App\Service\SecureSessionService')
        ;
    }

    private function confirmPasswordReset()
    {
        $submitButton = $this
            ->getClient()
            ->getCrawler()
            ->selectButton('user_confirmation[submit]')
        ;
        $form = $submitButton->form();
        $this
            ->getClient()
            ->submit($form)
        ;
    }

    public function testPasswordReset()
    {
        $this
            ->getClient()
            ->request('GET', '/not-authenticated/request-password-reset')
        ;
        $this->enterValidUsername();
        $this->uukpAuthorize();
        $submitButton = $this
            ->getClient()
            ->getCrawler()
            ->selectButton('password_update[submit]')
        ;
        $form = $submitButton->form(array(
            'password_update[password]' => 'mega',
            'password_update[passwordConfirmation]' => 'mega',
        ));
        $this
            ->getClient()
            ->submit($form)
        ;
        $this->logIn('louis', 'mega');
        $this->runLoggedInTests();
    }

    public function testU2fTokenReset()
    {
        $this->logIn('louis', 'hello');
        $this->runLoggedInTests();
        $this
            ->getClient()
            ->request('GET', '/authenticated/request-u2f-token-reset')
        ;
        
        $this->assertTrue($this->isRedirection());
        $this
            ->getClient()
            ->followRedirect()
        ;
        $this->assertTrue($this->isRedirection());
        $this->uukpAuthorize(true);
    }

    private function enterValidUsername()
    {
        $this
            ->getClient()
            ->followRedirect()
        ;
        $submitButton = $this
            ->getClient()
            ->getCrawler()
            ->selectButton('username[submit]')
        ;
        $usernameForm = $submitButton->form(array(
            'username[username]' => 'louis',
        ));
        $this->getClient()->submit($usernameForm);
        $this->assertTrue($this->getClient()->getResponse()->isRedirection());
    }

    private function enterValidU2fTokenResponse()
    {
        $submitButton = $this
            ->getClient()
            ->getCrawler()
            ->selectButton('u2f_login[submit]')
        ;
        $u2fAuthenticationForm = $submitButton
            ->form($this->getValidU2fTokenResponse())
        ;
        $this
            ->getClient()
            ->submit($u2fAuthenticationForm)
        ;
        $this->assertTrue($this->getClient()->getResponse()->isRedirection());
    }

    private function enterValidSecondU2fTokenResponse()
    {
        $submitButton = $this
            ->getClient()
            ->getCrawler()
            ->selectButton('u2f_login[submit]')
        ;
        $u2fAuthenticationForm = $submitButton
            ->form($this->getValidSecondU2fTokenResponse())
        ;
        $this
            ->getClient()
            ->submit($u2fAuthenticationForm)
        ;
        $this->assertTrue($this->getClient()->getResponse()->isRedirection());
    }

    private function checkNSignRequests(int $expectedNSignRequests)
    {
        $inlineScript = $this
            ->getClient()
            ->getCrawler()
            ->filter('script:contains("version")')
            ->text()
        ;
        $this->assertEquals(
            $expectedNSignRequests,
            substr_count($inlineScript, '{"version":"U2F_V2","challenge"'))
        ;
    }

    private function uukpAuthorize(bool $usernameAlreadySet = false)
    {
        $this
            ->getClient()
            ->followRedirect()
        ;
        $this->checkNSignRequests(3);
        $this->assertEquals(3, count($this->getContainer()->get('doctrine')->getManager()->getRepository(U2FToken::class)->getMemberRegistrations(1)));
        $this->enterValidSecondU2fTokenResponse();
        $this->getClient()->followRedirect();
        $this->checkNSignRequests(2);
        $this->enterValidU2fTokenResponse();
        $this->getClient()->followRedirect();
    }

    private function isRedirection(): bool
    {
        return $this
            ->getClient()
            ->getResponse()
            ->isRedirection()
        ;
    }
}