<?php

namespace App\Tests\Controller;

use App\Service\Adaptor\PasswordHasher;
use App\Tests\TestCaseTemplate;
use App\Tests\SecurityStrategyTrait;

class PasswordUpdateTest extends TestCaseTemplate
{
    use AuthenticationTrait;
    use SecurityStrategyTrait;

    const NEW_PASSWORD = 'new password';
 
    public function testPasswordUpdate()
    {
        $this->activateU2fSecurityStrategy();
        
        $this->u2fAuthenticate();
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
