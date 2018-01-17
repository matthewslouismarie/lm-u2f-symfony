<?php

namespace App\Tests\Controller;

use Firehed\U2F\SignRequest;
use App\FormModel\U2fAuthenticationRequest;

class MediumSecurityAuthorizerTest extends DbWebTestCase
{
    public function testLogin()
    {
        $this
            ->getClient()
            ->request('GET', '/not-authenticated/start-login')
        ;
        $this->assertTrue($this->getClient()->getResponse()->isRedirect());
        $this->getClient()->followRedirect();
        $this->assertRegExp('/\/all\/u2f-authorization\/medium-security\/[a-z0-9]+/', $this->getClient()->getRequest()->getUri());
        $button = $this
            ->getClient()
            ->getCrawler()
            ->selectButton('credential_authentication[submit]')
        ;
        $this->assertNotEquals(0, $button->count());
        $form = $button->form([
            'credential_authentication[username]' => 'louis',
            'credential_authentication[password]' => 'hello',
        ]);
        $this
            ->getClient()
            ->submit($form)
        ;
        $this->assertTrue($this->getClient()->getResponse()->isRedirect());
        $this->getClient()->followRedirect();
        $u2fButton = $this
            ->getClient()
            ->getCrawler()
            ->selectButton('new_u2f_authentication[submit]')
        ;
        $mock = $this->getContainer()->get('App\Service\MockU2fAuthenticationGenerator');
        $cycle = $mock->getNewCycle();
        $u2fForm = $u2fButton->form([
            'new_u2f_authentication[u2fTokenResponse]' => $cycle->getResponse(),
        ]);
        $submissionStack = $this->getContainer()->get('App\Service\SubmissionStack');
        $pos = strrpos($this->getClient()->getRequest()->getUri(), '/');
        $sid = substr($this->getClient()->getRequest()->getUri(), $pos + 1);
        $submissionStack->set($sid, 2, $cycle->getRequest(), U2fAuthenticationRequest::class);
    }
}
