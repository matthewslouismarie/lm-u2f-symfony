<?php

namespace App\Tests\Controller;

use App\Model\AuthorizationRequest;
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

    public function testAuthorizer()
    {
        $this
            ->getClient()
            ->request('GET', '/not-authenticated/request-password-reset')
        ;
        $this->enterValidUsername();
        $this->getClient()->followRedirect();
        $this->checkNSignRequests(3);
        $this->assertEquals(3, count($this->getContainer()->get('doctrine')->getManager()->getRepository(U2FToken::class)->getMemberRegistrations(1)));
        $this->enterValidSecondU2fTokenResponse();
        $this->getClient()->followRedirect();
        $this->checkNSignRequests(2);
        $this->enterValidU2fTokenResponse();
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
}