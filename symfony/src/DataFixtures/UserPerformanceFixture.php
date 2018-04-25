<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\AppSetting;
use App\Enum\SecurityStrategy;
use App\Enum\Setting;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpKernel\KernelInterface;

class UserPerformanceFixture extends Fixture
{
    private $sql;

    public function __construct(KernelInterface $kernel)
    {
        $this->sql = file_get_contents($kernel->getProjectDir().'/sql/sqltransformed.sql');
    }

    public function load(ObjectManager $manager)
    {
        $connection = $manager->getConnection();
        $statement = $connection->exec($this->sql);
    }
}

