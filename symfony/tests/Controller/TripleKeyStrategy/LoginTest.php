<?php

namespace App\Tests\TripleKeyStrategy;

use App\Tests\DbWebTestCase;
use Firehed\U2F\SignRequest;

class LoginTest extends DbWebTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->checkUrlStatusCode('/tks/login', 200);
        
        $upLoginGet = $this
            ->getClient()
            ->request('GET', '/tks/login')
        ;
        $upButton = $upLoginGet->selectButton('username_and_password[submit]');
        $form = $upButton->form(array(
            'username_and_password[username]' => 'louis',
            'username_and_password[password]' => 'hello',
        ));
        $postUpLogin = $this
            ->getClient()
            ->submit($form)
        ;
        $session = $this->getContainer()->get('App\Service\SecureSessionService');

        $signRequests = array();
        $signRequest = new SignRequest();
        $signRequest->setAppId('https://172.16.238.10');
        $signRequest->setChallenge('lXaq82clJBmXNnNWL1W6GA');
        $signRequest->setKeyHandle(base64_decode('v8IplXz0zSQUXVYjvSWNcP/70AamVDoaROr1UcREnWaARrRABftdhhaKTFsYTgOj5CH6BUYxztAN9qrU3WcBZg=='));
        $signRequests[1] = $signRequest;
        $requestId = $session->store(serialize($signRequests));
        $postUpLoginButton = $postUpLogin->selectButton('u2f_login[submit]');
        $form = $postUpLoginButton->form(array(
            'u2f_login[requestId]' => $requestId,
            'u2f_login[u2fTokenResponse]' => '{"keyHandle":"v8IplXz0zSQUXVYjvSWNcP_70AamVDoaROr1UcREnWaARrRABftdhhaKTFsYTgOj5CH6BUYxztAN9qrU3WcBZg","clientData":"eyJ0eXAiOiJuYXZpZ2F0b3IuaWQuZ2V0QXNzZXJ0aW9uIiwiY2hhbGxlbmdlIjoibFhhcTgyY2xKQm1YTm5OV0wxVzZHQSIsIm9yaWdpbiI6Imh0dHBzOi8vMTcyLjE2LjIzOC4xMCIsImNpZF9wdWJrZXkiOiJ1bnVzZWQifQ","signatureData":"AQAAAIkwRgIhAN1YRiOqMs1fOCOm7MuOxfYJ6qN7A8PdXrhEzejtw3gNAiEAgi0JJmODYRTN8qflhBNsAjuDkJz06hTUZi2LNbaU4gk"}',
        ));

        $this->getClient()->followRedirects(false);
        $validateLogin = $this->getClient()->submit($form);
    }

    public function testLogin()
    {
        

        $this->assertEquals('/public', $this->getClient()->getResponse()->getTargetUrl());

        $this->checkUrlStatusCode('/', 200);
        $this->checkUrlStatusCode('/mkps/registration', 403);
        $this->checkUrlStatusCode('/tks/username-and-password', 403);
        $this->checkUrlStatusCode('/tks/key-1', 403);
        $this->checkUrlStatusCode('/tks/key-2', 403);
        $this->checkUrlStatusCode('/tks/key-3', 403);
        $this->checkUrlStatusCode('/tks/finish-registration', 403);
        $this->checkUrlStatusCode('/tks/reset-registration', 403);
        $this->checkUrlStatusCode('/tks/login', 403);

        $this->checkUrlStatusCode('/add-u2f-token', 200);
        $this->checkUrlStatusCode('/logout', 200);
    }

    public function testLogout()
    {
        $firstCrawler = $this
            ->getClient()
            ->request('GET', '/logout')
        ;
        $button = $firstCrawler
            ->selectButton('user_confirmation[confirmation]')
        ;
        $form = $button->form();
        $secondCrawler = $this->getClient()->submit($form);
        $this->checkUrlStatusCode('/', 200);
        $this->checkUrlStatusCode('/mkps/registration', 200);
        $this->checkUrlStatusCode('/tks/username-and-password', 200);
        $this->checkUrlStatusCode('/tks/key-1', 302);
        $this->checkUrlStatusCode('/tks/key-2', 302);
        $this->checkUrlStatusCode('/tks/key-3', 302);
        $this->checkUrlStatusCode('/tks/finish-registration', 302);
        $this->checkUrlStatusCode('/tks/reset-registration', 302);
        $this->checkUrlStatusCode('/tks/login', 200);

        $this->checkUrlStatusCode('/add-u2f-token', 302);
        $this->checkUrlStatusCode('/logout', 302);
    }
}