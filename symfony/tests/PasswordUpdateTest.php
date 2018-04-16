<?php

namespace App\Tests;

use App\Service\Adaptor\PasswordHasher;
use App\Service\Form\Filler\PasswordUpdateFiller;

class PasswordUpdateTest extends TestCaseTemplate
{
    use LoginTrait;
    use SecurityStrategyTrait;

    const NEW_PASSWORD = 'new password';
 
    public function testPasswordUpdate()
    {
        $this->login();
        $this->activateU2fSecurityStrategy();
        $this->doGet('/authenticated/change-password');
        $this->submit(
            $this
                ->get(PasswordUpdateFiller::class)
                ->fillForm($this->getCrawler(), self::NEW_PASSWORD)
        );
        $this->followRedirect();
        $this->authenticateAsAdmin();
        $this->assertTrue(
            $this
                ->get(PasswordHasher::class)
                ->isPasswordValid($this->getLoggedInMember(), self::NEW_PASSWORD)
        );
    }
}
