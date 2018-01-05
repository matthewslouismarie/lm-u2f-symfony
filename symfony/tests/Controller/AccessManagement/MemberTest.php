<?php

namespace tests\Controller\AccessManagement;

use App\Tests\DbWebTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;

class MemberTest extends DbWebTestCase
{
    public function setUp()
    {
        parent::setUp();
        $crawler = $this->getClient()->request('GET', '/login');
        $button = $crawler->selectButton('login_form_log in');
        $form = $button->form(array(
            'login_form[username]' => 'louis',
            'login_form[password]' => 'hello',
        ));
        $this->getClient()->submit($form);
    }
    public function testPublicRoutes()
    {
        $this->checkUrlStatusCode('/', 200);
        $this->checkUrlStatusCode('/login', 403);
        $this->checkUrlStatusCode('/mkps/registration', 403);
        $this->checkUrlStatusCode('/tks/first-key', 403);
        $this->checkUrlStatusCode('/tks/username-and-password', 403);
        
    }

    public function testProtectedRoutes()
    {
        $this->checkUrlStatusCode('/add-u2f-token', 200);
        $this->checkUrlStatusCode('/logout', 200);
        $this->checkUrlStatusCode('/view-my-u2f-tokens', 200);
    }
}