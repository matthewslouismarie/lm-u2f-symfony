<?php

namespace App\Tests\Controller;

use App\Model\AuthorizationRequest;

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
}