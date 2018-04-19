<?php

declare(strict_types=1);

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
        $this->followRedirect();
        $this->authenticateAsAdmin();
        $this->submit(
            $this
                ->get(PasswordUpdateFiller::class)
                ->fillForm($this->getCrawler(), self::NEW_PASSWORD)
        );

        $this->assertTrue(
            $this
                ->get(PasswordHasher::class)
                ->isPasswordValid($this->getLoggedInMember(), self::NEW_PASSWORD)
        );
    }
}
