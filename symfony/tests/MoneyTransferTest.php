<?php

namespace App\Tests;

use App\DataFixtures\AppFixture;
use App\Service\Form\Filler\ValidPasswordFiller;
use App\Tests\SecurityStrategyTrait;
use App\Tests\TestCaseTemplate;
use App\Service\Form\Filler\UserConfirmationFiller;

class MoneyTransferTest extends TestCaseTemplate
{
    use LoginTrait;
    use SecurityStrategyTrait;

    public function testMoneyTransferPwd()
    {
        $this->activatePwdSecurityStrategy();
        $this->login();

        $this->doGet('/authenticated/transfer-money');
        $this->submit($this
                ->get(UserConfirmationFiller::class)
                ->fillForm($this->getCrawler()))
        ;
        $this->followRedirect();
        $this->authenticateAsAdmin();
        $this->assertContains(
            'success',
            $this
                ->getClient()
                ->getResponse()
                ->getContent())
        ;
    }

    public function testMoneyTransferU2f()
    {
        $this->login();
        $this->activateU2fSecurityStrategy();

        $this->doGet('/authenticated/transfer-money');
        $this->submit($this
                ->get('App\Service\Form\Filler\UserConfirmationFiller')
                ->fillForm($this->getCrawler()))
        ;
        $this->followRedirect();
        $this->authenticateAsAdmin();
        $this->assertContains(
            'success',
            $this
                ->getClient()
                ->getResponse()
                ->getContent())
        ;
    }
}
