<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\AppSetting;
use App\Enum\SecurityStrategy;
use App\Enum\Setting;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class ConfigFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $manager->persist(new AppSetting(Setting::ALLOW_MEMBER_TO_MANAGE_U2F_KEYS, true));
        $manager->persist(new AppSetting(Setting::ALLOW_PWD_LOGIN, true));
        $manager->persist(new AppSetting(Setting::ALLOW_U2F_LOGIN, true));
        $manager->persist(new AppSetting(Setting::N_U2F_KEYS_LOGIN, 2));
        $manager->persist(new AppSetting(Setting::N_U2F_KEYS_POST_AUTH, 3));
        $manager->persist(new AppSetting(Setting::N_U2F_KEYS_REG, 1));
        $manager->persist(new AppSetting(Setting::PARTICIPANT_ID, 'Undefined'));
        $manager->persist(new AppSetting(Setting::PWD_MIN_LENGTH, 0));
        $manager->persist(new AppSetting(Setting::PWD_NUMBERS, true));
        $manager->persist(new AppSetting(Setting::PWD_SPECIAL_CHARS, true));
        $manager->persist(new AppSetting(Setting::PWD_UPPERCASE, true));
        $manager->persist(new AppSetting(Setting::PWD_ENFORCE_MIN_LENGTH, true));
        $manager->persist(new AppSetting(Setting::SEC_HIGH_PWD, true));
        $manager->persist(new AppSetting(Setting::SEC_HIGH_U2F, true));
        $manager->persist(new AppSetting(Setting::SEC_HIGH_U2F_N, 1));
        $manager->persist(new AppSetting(Setting::SEC_HIGH_BOTH, true));
        $manager->persist(new AppSetting(Setting::SEC_MEDM_PWD, true));
        $manager->persist(new AppSetting(Setting::SEC_MEDM_U2F, true));
        $manager->persist(new AppSetting(Setting::SEC_MEDM_U2F_N, 1));
        $manager->persist(new AppSetting(Setting::SEC_MEDM_BOTH, false));
        $manager->persist(new AppSetting(Setting::SECURITY_STRATEGY, SecurityStrategy::U2F));
        $manager->persist(new AppSetting(Setting::USER_STUDY_MODE_ACTIVE, false));
        $manager->flush();
    }
}
