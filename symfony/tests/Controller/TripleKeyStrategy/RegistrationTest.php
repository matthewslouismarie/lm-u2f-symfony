<?php

namespace App\Tests\Controller\TripleKeyStrategy;

use App\Factory\MemberFactory;
use App\Tests\DbWebTestCase;

class RegistrationTest extends DbWebTestCase
{
    public function testUsernameAndPassword()
    {
        $this->checkUrlStatusCode('/tks/username-and-password', 200);
        $this->checkUrlStatusCode('/tks/key-1', 302);
        $this->checkUrlStatusCode('/tks/key-2', 302);
        $this->checkUrlStatusCode('/tks/key-3', 302);        
        $this->usernameAndPassword();

        $this->checkUrlStatusCode('/tks/username-and-password', 200);
        $this->checkUrlStatusCode('/tks/key-1', 200);
        $this->checkUrlStatusCode('/tks/key-2', 302);
        $this->checkUrlStatusCode('/tks/key-3', 302);     
        $this->key(1);

        // $this->checkUrlStatusCode('/tks/username-and-password', 200);
        // $this->checkUrlStatusCode('/tks/key-1', 200);
        // $this->checkUrlStatusCode('/tks/key-2', 200);
        // $this->checkUrlStatusCode('/tks/key-3', 302);     
        // $this->key(2);

        // $this->checkUrlStatusCode('/tks/username-and-password', 200);
        // $this->checkUrlStatusCode('/tks/key-1', 200);
        // $this->checkUrlStatusCode('/tks/key-2', 200);
        // $this->checkUrlStatusCode('/tks/key-3', 200);
        // $this->key(3);
    }

    private function usernameAndPassword()
    {
        $session = $this->getContainer()->get('session');
        $hasher = $this->getContainer()->get('security.password_encoder');
        $firstCrawler = $this
            ->getClient()
            ->request('GET', '/tks/username-and-password');
        $button = $firstCrawler->selectButton('registration[submit]');
        $form = $button->form(array(
            'registration[username]' => 'johndoe',
            'registration[password]' => 'password',
        ));
        $secondCrawler = $this->getClient()->submit($form);
        $sessionMember = $session->get('tks_member');
        $this->assertEquals(
            'johndoe',
            $sessionMember->getUsername()
        );
        $this->assertTrue($hasher->isPasswordValid($sessionMember, 'password'));
        $this->assertFalse($hasher->isPasswordValid($sessionMember, 'pssword'));
        $this->checkUrlStatusCode('/tks/key-1', 200);
    }

    private function key(int $keyNo)
    {
        $firstCrawler = $this
            ->getClient()
            ->request('GET', '/tks/key-'.$keyNo);
        $button = $firstCrawler->selectButton('u2_f_token_registration[submit]');
        $form = $button->form(array(
            'u2_f_token_registration[u2fTokenResponse]' => 'invalid response'
        ));
        $secondCrawler = $this->getClient()->submit($form);
        
        $this->assertContains(
            'error',
            $this->getClient()->getResponse()->getContent()
        );
    }
}