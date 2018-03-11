<?php

namespace App\Tests\Controller;

use App\Service\Adaptor\PasswordHasher;
use App\Tests\TestCaseTemplate;

class PasswordUpdateTest extends TestCaseTemplate
{
    use AuthenticationTrait;

    const NEW_PASSWORD = 'new password';

    public function testPasswordUpdate()
    {
        $this->authenticateAsLouis();
        $this->doGet('/change-password');
        $this->submit(
            $this
                ->get('App\Service\Form\Filler\PasswordUpdateFiller')
                ->fillForm($this->getCrawler(), self::NEW_PASSWORD)
        );
        $this->followRedirect();
        $this->followRedirect();
        $this->submit(
            $this
                ->get('App\Service\Form\Filler\U2fAuthenticationFiller1')
                ->fillForm($this->getCrawler(), $this->getUriLastPart())
        );
        $this->followRedirect();
        $this->assertTrue(
            $this
                ->get(PasswordHasher::class)
                ->isPasswordValid($this->getLoggedInMember(), self::NEW_PASSWORD)
        );
    }
}
