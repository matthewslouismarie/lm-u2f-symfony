<?php

namespace App\Tests\Controller;

use App\Model\AuthorizationRequest;
use Firehed\U2F\SignRequest;

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
        $authorizationRequest = new AuthorizationRequest(false, 'login_success_route');
        $sessionId = $this
            ->session
            ->store($authorizationRequest)
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
        
        $session = $this
            ->getContainer()
            ->get('App\Service\SecureSessionService')
        ;
        $signRequests = array();
        $signRequest = new SignRequest();
        $signRequest->setAppId('https://172.16.238.10');
        $signRequest->setChallenge('lXaq82clJBmXNnNWL1W6GA');
        $signRequest->setKeyHandle(base64_decode('v8IplXz0zSQUXVYjvSWNcP/70AamVDoaROr1UcREnWaARrRABftdhhaKTFsYTgOj5CH6BUYxztAN9qrU3WcBZg=='));
        $signRequests[1] = $signRequest;
        $requestId = $session->store(serialize($signRequests));
        $postUpLoginButton = $this
            ->getClient()
            ->getCrawler()
            ->selectButton('u2f_login[submit]')
        ;
        $form = $postUpLoginButton->form(array(
            'u2f_login[u2fAuthenticationRequestId]' => $requestId,
            'u2f_login[u2fTokenResponse]' => '{"keyHandle":"v8IplXz0zSQUXVYjvSWNcP_70AamVDoaROr1UcREnWaARrRABftdhhaKTFsYTgOj5CH6BUYxztAN9qrU3WcBZg","clientData":"eyJ0eXAiOiJuYXZpZ2F0b3IuaWQuZ2V0QXNzZXJ0aW9uIiwiY2hhbGxlbmdlIjoibFhhcTgyY2xKQm1YTm5OV0wxVzZHQSIsIm9yaWdpbiI6Imh0dHBzOi8vMTcyLjE2LjIzOC4xMCIsImNpZF9wdWJrZXkiOiJ1bnVzZWQifQ","signatureData":"AQAAAIkwRgIhAN1YRiOqMs1fOCOm7MuOxfYJ6qN7A8PdXrhEzejtw3gNAiEAgi0JJmODYRTN8qflhBNsAjuDkJz06hTUZi2LNbaU4gk"}',
        ));

        $validateLogin = $this
            ->getClient()
            ->submit($form)
        ;
        $this->getClient()->followRedirect();
        $this->assertRegExp('/^http:\/\/localhost\/not-authenticated\/login\/[a-z0-9]+$/', $this->getClient()->getRequest()->getUri());
    }
}