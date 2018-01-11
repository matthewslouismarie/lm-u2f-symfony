<?php

namespace App\Tests\Controller;

use App\Entity\U2FToken;
use Firehed\U2F\SignRequest;

abstract class AbstractAccessManagementTestCase extends DbWebTestCase
{
    private $u2fCount = 0;

    public function logIn(string $username, string $password)
    {
        $upLoginGet = $this
            ->getClient()
            ->request('GET', '/not-authenticated/login')
        ;

        $this->getClient()->followRedirect();

        $this->upLogInFromUpPage($username, $password);

        if (!$this->getClient()->getResponse()->isRedirection()) {
            return;
        }
        
        $requestId = $this->storeInSessionU2fToken(true);

        $this->getClient()->followRedirect();

        $this->ukLogInFromUkPage($requestId);
        
        if (!$this->getClient()->getResponse()->isRedirection()) {
            return;
        }
        
        $this->getClient()->followRedirect();
        $this->assertRegExp(
            '/^http:\/\/localhost\/not-authenticated\/finish-login\/[a-z0-9]+$/',
            $this->getClient()->getRequest()->getUri());
        
        $submit = $this
            ->getClient()
            ->getCrawler()
            ->selectButton('login_request[submit]');
        $form = $submit->form();
        $this->getClient()->submit($form);
    }

    public function logOut()
    {
        $logout = $this
            ->getClient()
            ->request('GET', '/authenticated/log-out');
        $button = $logout->selectButton('user_confirmation[submit]');
        $form = $button->form();
        $this->getClient()->submit($form);
    }

    public function runLoggedOutTests()
    {
        $this->checkUrlStatusCode(
            '/not-authenticated/login',
            302)
        ;
        $this->checkUrlStatusCode(
            '/authenticated/change-password',
            302)
        ;
        $this->checkUrlStatusCode(
            '/authenticated/log-out',
            302)
        ;
    }

    public function runLoggedInTests()
    {
        $this->checkUrlStatusCode(
            '/not-authenticated/login',
            302)
        ;
        $this->checkUrlStatusCode(
            '/authenticated/change-password',
            200)
        ;
        $this->checkUrlStatusCode(
            '/authenticated/log-out',
            200)
        ;
    }

    public function resetU2fCounter()
    {
        $doctrine = $this->getContainer()->get('doctrine');
        $ubs = $this->getContainer()->get('App\Service\U2FTokenBuilderService');
        $repo = $doctrine->getRepository(U2FToken::class);
        $oldU2fTokens = $repo->findAll();
        $om = $doctrine->getManager();
        foreach ($oldU2fTokens as $oldU2fToken) {
            $ub = $ubs->createBuilder($oldU2fToken);
            $newU2fToken = $ub->setCounter(0);
            $om->detach($oldU2fToken);
            $om->persist($newU2fToken);
        }
        $om->flush();
    }

    public function upLogInFromUpPage(
        string $username,
        string $password)
    {
        $button = $this
            ->getClient()
            ->getCrawler()
            ->selectButton('username_and_password[submit]')
        ;
        $form = $button->form(array(
            'username_and_password[username]' => $username,
            'username_and_password[password]' => $password,
        ));
        $secondCrawler = $this->getClient()->submit($form);
    }

    public function ukLogInFromUkPage(string $requestId)
    {
        $content = $this
            ->getClient()
            ->getResponse()
            ->getContent()
        ;
        $postUpLoginButton = $this
            ->getClient()
            ->getCrawler()
            ->selectButton('u2f_login[submit]')
        ;
        $form = $postUpLoginButton->form($this->getValidU2fTokenResponse());
        
        $validateLogin = $this
            ->getClient()
            ->submit($form)
        ;
        $this->checkU2fTokens();
    }

    public function storeInSessionU2fToken(bool $isValid): string
    {
        $sSession = $this
            ->getContainer()
            ->get('App\Service\SecureSessionService')
        ;
        $signRequests = array();
        $signRequest = new SignRequest();
        if ($isValid) {
            $signRequest->setAppId('https://172.16.238.10');
        } else {
            $signRequest->setAppId('https://172.15.238.10');
        }
        $signRequest->setChallenge('lXaq82clJBmXNnNWL1W6GA');
        $signRequest->setKeyHandle(base64_decode('v8IplXz0zSQUXVYjvSWNcP/70AamVDoaROr1UcREnWaARrRABftdhhaKTFsYTgOj5CH6BUYxztAN9qrU3WcBZg=='));
        $signRequests[1] = $signRequest;
        return $sSession->storeArray($signRequests);
    }

    public function getValidU2fTokenResponse(): array
    {
        $u2fAuthenticationRequestId = $this->storeInSessionU2fToken(true);
        $submitButton = $this
            ->getClient()
            ->getCrawler()
            ->selectButton('u2f_login[submit]');

        return array(
            'u2f_login[u2fAuthenticationRequestId]' => $u2fAuthenticationRequestId,
            'u2f_login[u2fTokenResponse]' => '{"keyHandle":"v8IplXz0zSQUXVYjvSWNcP_70AamVDoaROr1UcREnWaARrRABftdhhaKTFsYTgOj5CH6BUYxztAN9qrU3WcBZg","clientData":"eyJ0eXAiOiJuYXZpZ2F0b3IuaWQuZ2V0QXNzZXJ0aW9uIiwiY2hhbGxlbmdlIjoibFhhcTgyY2xKQm1YTm5OV0wxVzZHQSIsIm9yaWdpbiI6Imh0dHBzOi8vMTcyLjE2LjIzOC4xMCIsImNpZF9wdWJrZXkiOiJ1bnVzZWQifQ","signatureData":"AQAAAIkwRgIhAN1YRiOqMs1fOCOm7MuOxfYJ6qN7A8PdXrhEzejtw3gNAiEAgi0JJmODYRTN8qflhBNsAjuDkJz06hTUZi2LNbaU4gk"}',
        );
    }

    public function checkU2fTokens()
    {
        $u2fToken = $this
            ->getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository(U2FToken::class)
            ->find(1);
        $this->assertNotNull($u2fToken);
    }
}