<?php

namespace App\Tests\Controller;

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
    }
}