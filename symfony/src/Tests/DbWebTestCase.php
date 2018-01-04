<?php

namespace App\Tests;

use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Doctrine\Bundle\FixturesBundle\Loader\SymfonyFixturesLoader;

/**
 * @todo Should be able to automatically load all fixtures.
 * @todo Check that entity_manager is not obsolete.
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
        $em = $container->get('doctrine.orm.entity_manager');
        $this->metadatas = $em->getMetadataFactory()->getAllMetadata();
        $this->schemaTool = new SchemaTool($em);
        $this->schemaTool->createSchema($this->metadatas);
        $fl = new SymfonyFixturesLoader($container);
        $fl->addFixtures(array($container->get('App\DataFixtures\MembersFixture')));
        $fixtures = $fl->getFixtures();
        $purger = new ORMPurger($em);
        $executor = new ORMExecutor($em, $purger);
        $executor->execute($fixtures, false);
    }

    public function tearDown()
    {
        $this->schemaTool->dropSchema($this->metadatas);
    }

    /**
     * @todo Move in a service?
     */
    public function checkUrlStatusCode($url, $expectedStatusCode)
    {
        $crawler = $this->client->request('GET', $url);
        $this->assertEquals(
            $expectedStatusCode,
            $this->client->getResponse()->getStatusCode()
        );
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