<?php

namespace App\Tests\Controller;

class PasswordUpdateTest extends AbstractAccessManagementTestCase
{
    private $u2z;

    public function setUp()
    {
        parent::setUp();
        $this->om = $this
            ->getContainer()
            ->get('doctrine')
            ->getManager()
        ;
    }

    /**
     * @todo Add password confirmation.
     */
    public function testPasswordUpdate()
    {
        $this->runLoggedOutTests();
        $this->logIn('louis', 'ello');
        $this->runLoggedOutTests();

        $this->resetU2fCounter();

        $this->logIn('louis', 'hello');
        $this->runLoggedInTests();
        $this->changePassword();
        $this->logOut();

        $this->resetU2fCounter();

        $this->logIn('louis', 'meow');
        $this->runLoggedInTests();
    }

    public function changePassword()
    {
        $firstCrawler = $this
            ->getClient()
            ->request('GET', '/authenticated/change-password')
        ;
        $button = $firstCrawler->selectButton('password_update[submit]');
        $wrongForm = $button->form(array(
            'password_update[password]' => 'meow',
            'password_update[passwordConfirmation]' => 'something else',
        ));
        $secondCrawler = $this->getClient()->submit($wrongForm);

        $this->assertEquals(200, $this->getClient()->getResponse()->getStatusCode());

        $validForm = $button->form(array(
            'password_update[password]' => 'meow',
            'password_update[passwordConfirmation]' => 'meow',
        ));
        $postRequest = $this->getClient()->submit($validForm);
    }
}
