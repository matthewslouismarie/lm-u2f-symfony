<?php

namespace App\Tests\Controller;

use App\Service\Adaptor\PasswordHasher;
use App\Tests\TestCaseTemplate;
use App\Tests\U2fSecurityStrategyTrait;

class PasswordUpdateTest extends TestCaseTemplate
{
    use AuthenticationTrait;
    use U2fSecurityStrategyTrait;

    const NEW_PASSWORD = 'new password';

    public function setUp()
    {
        parent::setUp();
        $this->activateU2fSecurityStrategy();
    }
        
    public function testPasswordUpdate()
    {
        $this->authenticateAsLouis();
        $this->doGet('/authenticated/change-password');
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
