<?php

namespace App\Tests;

use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\Bundle\FixturesBundle\Loader\SymfonyFixturesLoader;

/**
 * @todo Should be able to automatically load all fixtures.
 */
abstract class DbWebTestCase extends WebTestCase
{
    private $client;

    private $schemaTool;

    private $metadatas;

    public function setUp()
    {
        $this->client = static::createClient();
        $container = self::$kernel->getContainer();
        $om = $container->get('doctrine')->getManager();
        $this->metadatas = $om->getMetadataFactory()->getAllMetadata();
        $this->schemaTool = new SchemaTool($om);
        $this->schemaTool->createSchema($this->metadatas);
        $fl = new SymfonyFixturesLoader($container);
        $fl->addFixtures(array($container->get('App\DataFixtures\MembersFixture')));
        $fixtures = $fl->getFixtures();
        $purger = new ORMPurger($om);
        $executor = new ORMExecutor($om, $purger);
        $executor->execute($fixtures, false);
    }

    public function tearDown()
    {
        $this->schemaTool->dropSchema($this->metadatas);
    }

    public function getClient()
    {
        return $this->client;
    }

    public function getContainer()
    {
        return self::$kernel->getContainer();
    }
}
