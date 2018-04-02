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
        $this->pwdAuthenticate();

        $this->doGet('/authenticated/transfer-money');
        $this->submit($this
                ->get('App\Service\Form\Filler\UserConfirmationFiller')
                ->fillForm($this->getCrawler()))
        ;
        $this->followRedirect();
        $this->followRedirect();
        $this->submit($this
            ->get('App\Service\Form\Filler\ValidPasswordFiller')
            ->fillForm($this->getCrawler(), 'hell'))
        ;
        $this->assertFalse($this->isRedirect());
        $this->submit($this
            ->get('App\Service\Form\Filler\ValidPasswordFiller')
            ->fillForm($this->getCrawler(), 'hello'))
        ;
        $this->followRedirect();
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
        $this->activateU2fSecurityStrategy();
        $this->u2fAuthenticate();

        $this->doGet('/authenticated/transfer-money');
        $this->submit($this
                ->get('App\Service\Form\Filler\UserConfirmationFiller')
                ->fillForm($this->getCrawler()))
        ;
        $this->followRedirect();
        $this->followRedirect();
        $this->submit($this
            ->get('App\Service\Form\Filler\U2fAuthenticationFiller1')
            ->fillForm($this->getCrawler(), $this->getUriLastPart()))
        ;
        $this->followRedirect();
        $this->assertContains(
            'success',
            $this
                ->getClient()
                ->getResponse()
                ->getContent())
        ;
    }
}
