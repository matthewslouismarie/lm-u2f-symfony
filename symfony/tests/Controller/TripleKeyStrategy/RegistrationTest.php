<?php

namespace App\Tests\Controller\TripleKeyStrategy;

use App\Factory\MemberFactory;
use App\Tests\DbWebTestCase;

class RegistrationTest extends DbWebTestCase
{
    public function testUsernameAndPassword()
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
    }

    public function testFirstKeyRegistration()
    {
        $firstCrawler = $this
            ->getClient()
            ->request('GET', '/mkps/master-pair-first-key');
        $button = $firstCrawler->selectButton('u2_f_token_registration[submit]');
        $form = $button->form(array(
            'u2_f_token_registration[name]' => 'My First Key!!',
            'u2_f_token_registration[u2fTokenResponse]' => 'invalid response'
        ));
        $secondCrawler = $this->getClient()->submit($form);
        
        $this->assertContains(
            'error',
            $this->getClient()->getResponse()->getContent()
        );
    }
}