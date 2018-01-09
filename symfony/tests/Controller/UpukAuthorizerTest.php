<?php

namespace App\Tests\Controller;

use App\Model\UserRequestedAction;

class UpukAuthorizerTest extends DbWebTestCase
{
    private $session;

    public function setUp()
    {
        parent::setUp();
        $this->session = $this
            ->getContainer()
            ->get('App\Service\SecureSessionService')
        ;
    }

    public function testUpukAuthorizer()
    {
        $userRequestedAction = new UserRequestedAction(false, '/');
        $sessionId = $this
            ->session
            ->store($userRequestedAction)
        ;
        $crawler = $this
            ->getClient()
            ->request('GET', '/all/u2f-authorization/upuk/up/'.$sessionId)
        ;
        $statusCode = $this
            ->getClient()
            ->getResponse()
            ->getStatusCode()
        ;
        $this->assertEquals(200, $statusCode);

        $button = $crawler->selectButton('username_and_password[submit]')
        ;
        $form = $button->form(array(
            'username_and_password[username]' => 'louis',
            'username_and_password[password]' => 'hello',
        ));
        $secondCrawler = $this->getClient()->submit($form);
        $this->getClient()->followRedirect();
        $content = $this->getClient()->getResponse()->getContent();
        $this->assertContains('Please activate your U2F key.', $content);
    }
}