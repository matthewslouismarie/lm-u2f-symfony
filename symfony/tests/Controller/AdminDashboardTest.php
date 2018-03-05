<?php

namespace App\Tests\Controller;

use App\Tests\TestCaseTemplate;

class AdminDashboardTest extends TestCaseTemplate
{
    private function authenticateAsLouis()
    {
        $this->doGet('/not-authenticated/authenticate');
        $this->assertIsRedirect();
        $this->followRedirect();
        $this->followRedirect();
        $this->submit(
            $this
            ->get('App\Service\Form\Filler\ExistingUsernameFiller')
            ->fillForm($this->getCrawler(), 'louis'))
        ;
        $this->followRedirect();

        $this->submit(
            $this
            ->get('App\Service\Form\Filler\U2fAuthenticationFiller1')
            ->fillForm($this->getCrawler(), $this->getUriLastPart()))
        ;

        $this->followRedirect();
        $this->assertTrue($this->isAdmin());
    }

    public function testAdmin()
    {
        $this->doGet('/admin');
        $this->assertEquals(302, $this->getHttpStatusCode());
        $this->authenticateAsLouis();
        $this->doGet('/admin');
        $this->assertEquals(200, $this->getHttpStatusCode());        
    }
}
