<?php

namespace App\Tests\Controller;

use App\Tests\Controller\AuthenticationTrait;
use App\Tests\SecurityStrategyTrait;
use App\Tests\TestCaseTemplate;

class MoneyTransferTest extends TestCaseTemplate
{
    use AuthenticationTrait;
    use SecurityStrategyTrait;

    public function testMoneyTransferPwd()
    {
        $this->activatePwdSecurityStrategy();
        $this->pwdAuthenticateAsLouis();

        $this->doGet('/authenticated/transfer-money');
        $this->submit($this
                ->get('App\Service\Form\Filler\UserConfirmationFiller')
                ->fillForm($this->getCrawler()))
        ;
        $this->followRedirect();
        $this->followRedirect();
        $this->submit($this
            ->get('App\Service\Form\Filler\CredentialAuthenticationFiller')
            ->fillForm($this->getCrawler(), 'hello', 'louis'))
        ;
        $this->followRedirect();
        $this->assertContains(
            'The money transfer was successful.',
            $this
                ->getClient()
                ->getResponse()
                ->getContent())
        ;
    }
}
