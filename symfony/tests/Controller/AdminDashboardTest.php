<?php

namespace App\Tests\Controller;

use App\Service\AppConfigManager;
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

    public function testAdminOptions()
    {
        $this->authenticateAsLouis();
        $this->doGet('/admin/registration');
        $button = $this
            ->getCrawler()
            ->selectButton('registration_config[submit]')
        ;
        $this->submit($button->form([
            'registration_config[nU2fKeys]' => 2,
        ]));
        $this->assertEquals(
            2,
            $this
                ->getAppConfigManager()
                ->getIntSetting(AppConfigManager::REG_N_U2F_KEYS))
        ;
    }
}
