<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

class Anonymous extends WebTestCase
{
    private $client;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    public function testPublicRoutes()
    {
        $this->checkUrlStatusCode('/', 200);
        $this->checkUrlStatusCode('/login', 200);
        
    }

    public function testProtectedRoutes()
    {
        $this->checkUrlStatusCode('/logout', 302);
        $this->checkUrlStatusCode('/view-my-u2f-tokens', 302);
        $this->checkUrlStatusCode('/add-u2f-token', 302);
        $this->checkUrlStatusCode('/delete-u2f-token/nom', 302);
    }

    /**
     * @todo Move in abstract class.
     */
    private function checkUrlStatusCode($url, $expectedStatusCode)
    {
        $crawler = $this->client->request('GET', $url);
        $this->assertEquals(
            $expectedStatusCode,
            $this->client->getResponse()->getStatusCode()
        );
    }
}