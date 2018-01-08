<?php

namespace App\Tests\Controller\TripleKeyStrategy;

use App\Tests\DbWebTestCase;

class UpClearanceTest extends DbWebTestCase
{
    public function testFirstClearance()
    {
        $this->loggedOut();
        $this->logIn();
    }

    private function loggedOut()
    {
        $this->checkUrlStatusCode('/tks-0/not-authenticated/authenticate', 200);
        $this->checkUrlStatusCode('/tks-0/authenticated/change-password', 302);
    }

    private function logIn()
    {
        $authenticationCrawler = $this
            ->getClient()
            ->request('GET', '/tks-0/authenticated/change-password')
        ;
        $authenticationCrawler = $this->getClient()->followRedirect();
        $button = $this
            ->getClient()
            ->getCrawler()
            ->selectButton('username_and_password[submit]')
        ;
        $form = $button->form(array(
            'username_and_password[username]' => 'louis',
            'username_and_password[password]' => 'hello'
        ));
        $submittedCrawler = $this
            ->getClient()
            ->submit($form)
        ;
        $this
            ->getClient()
            ->followRedirect()
        ;
        $this->assertEquals('http://localhost/tks-0/authenticated/change-password', $this
            ->getClient()
            ->getRequest()
            ->getUri()
        );
    }

    private function loggedIn()
    {

    }
}