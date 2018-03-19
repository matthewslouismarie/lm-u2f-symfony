<?php

namespace App\Tests\Controller;

use App\Tests\TestCaseTemplate;

class AccountDeletionTest extends TestCaseTemplate
{
    use AuthenticationTrait;

    public function testAccountDeletion()
    {
        $this->authenticateAsLouis();
        $this->doGet('/authenticated/my-account/delete-account');
        $this->assertContains(
            'Do you really want to delete your account?',
            $this->getClient()->getResponse()->getContent())
        ;
        $this->submit($this
            ->get('App\Service\Form\Filler\UserConfirmationFiller')
            ->fillForm($this->getCrawler()))
        ;
        $this->performHighSecurityAuthenticationAsLouis();
    }
}
