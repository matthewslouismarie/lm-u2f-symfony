<?php

namespace App\Tests\Controller;

class PasswordUpdateTest extends AbstractUpukTestCase
{
    /**
     * @todo Add password confirmation.
     */
    public function testPasswordUpdate()
    {
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
        $getRequest = $this
            ->getClient()
            ->request('GET', '/tks-upuk/authenticated/change-password')
        ;
        $button = $getRequest->selectButton('password_update[submit]');
        $form = $button->form(array(
            'password_update[password]' => 'meow',
        ));
        $postRequest = $this->getClient()->submit($form);
    }
}