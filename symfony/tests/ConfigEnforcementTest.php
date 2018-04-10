<?php

namespace App\Tests;

use App\Entity\U2fToken;
use App\Enum\Setting;
use App\Service\AppConfigManager;
use App\Tests\TestCaseTemplate;

class ConfigEnforcementTest extends TestCaseTemplate
{
    use LoginTrait;

    const U2F_REG_NEEDED_MSG = 'Before being able to use the website again, you need to register 1 new U2F key(s).';

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

    public function testMinU2fDeviceN()
    {
        $this->login();
        $em = $this
            ->get('doctrine')
            ->getManager()
        ;
        $nU2fRegistrations = $em
            ->getRepository(U2fToken::class)
            ->count([
                'member' => $this->getLoggedInMember(),
            ])
        ;
        $this
            ->get(AppConfigmanager::class)
            ->set(Setting::N_U2F_KEYS_POST_AUTH, $nU2fRegistrations + 1)
        ;
        $this->doGet('/');
        $this->assertContains(
            self::U2F_REG_NEEDED_MSG,
            $this
                ->getClient()
                ->getResponse()
                ->getContent())
        ;
        $this->doGet('/authenticated/register-u2f-device');
        $this->assertNotContains(
            self::U2F_REG_NEEDED_MSG,
            $this
                ->getClient()
                ->getResponse()
                ->getContent())
        ;
    }
}
