<?php

namespace App\Tests\Controller\AccessManagement;

use App\Tests\DbWebTestCase;
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

class Anonymous extends DbWebTestCase
{
    public function testPublicRoutes()
    {
        $this->checkUrlStatusCode('/', 200);
        $this->checkUrlStatusCode('/login', 200);
        $this->checkUrlStatusCode('/mkps/registration', 200);
        $this->checkUrlStatusCode('/tks/first-key', 302);
        $this->checkUrlStatusCode('/tks/username-and-password', 200);
    }

    public function testProtectedRoutes()
    {
        $this->checkUrlStatusCode('/logout', 302);
        $this->checkUrlStatusCode('/view-my-u2f-tokens', 302);
        $this->checkUrlStatusCode('/add-u2f-token', 302);
        $this->checkUrlStatusCode('/delete-u2f-token/nom', 302);
    }
}