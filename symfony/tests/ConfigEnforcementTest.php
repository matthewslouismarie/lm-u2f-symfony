<?php

namespace App\Tests;

use App\Entity\U2fToken;
use App\Enum\Setting;
use App\Service\AppConfigManager;
use App\Tests\TestCaseTemplate;

class ConfigEnforcementTest extends TestCaseTemplate
{
    use LoginTrait;

    public function testDeviceRemoval()
    {
        $this->login();

        $doctrineManager = $this
            ->get('doctrine')
            ->getManager()
        ;
        $u2fRegistrationRepository = $doctrineManager
            ->getRepository(U2fToken::class)
        ;
        $u2fRegistrations = $u2fRegistrationRepository
            ->findBy([
                'member' => $this->getLoggedInMember(),
            ])
        ;
        foreach ($u2fRegistrations as $u2fRegistration) {
            $doctrineManager->remove($u2fRegistration);
        }
        $doctrineManager->flush();
        $this->doGet('/');
        $nU2fKeysMin = $this
            ->get(AppConfigManager::class)
            ->getIntSetting(Setting::N_U2F_KEYS_POST_AUTH)
        ;
        if ($nU2fKeysMin > 0) {
            $this->assertContains(
                'you need to register',
                $this->getCrawler()->text()
            );
        }
    }
}
