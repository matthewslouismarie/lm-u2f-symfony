<?php

namespace tests\Controller\AccessManagement;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MemberTest extends WebTestCase
{
    private $client;

    public function setUp()
    {
        $this->client = static::createClient();
        
        $crawler = $this->client->request('GET', '/login');
        $button = $crawler->selectButton('login_form_log in');
        $form = $button->form(array(
            'login_form[username]' => 'louis',
            'login_form[password]' => 'hello',
        ));
        $this->client->submit($form);
    }

    public function testPublicRoutes()
    {
        $this->checkUrlStatusCode('/', 200);
        $this->checkUrlStatusCode('/login', 403);
        
    }

    public function testProtectedRoutes()
    {
        $this->checkUrlStatusCode('/add-u2f-token', 200);
        $this->checkUrlStatusCode('/logout', 200);
        $this->checkUrlStatusCode('/view-my-u2f-tokens', 200);
    }

    /**
     * @todo Move in abstract class.
     */
    private function checkUrlStatusCode($url, $expectedStatusCode)
    {
        $crawler = $this->client->request('GET', $url);
        $this->assertEquals(
            $expectedStatusCode,
            $this->client->getResponse()->getStatusCode()
        );
    }
}