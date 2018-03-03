<?php

namespace App\Tests;

use App\Service\SecureSession;
use App\Service\Mocker\U2fAuthenticationMocker;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;

abstract class TestCaseTemplate extends DbWebTestCase
{
    public function assertIsRedirect(): void
    {
        $isRedirect = $this
            ->getClient()
            ->getResponse()
            ->isRedirect()
        ;
        $this->assertTrue($isRedirect);
    }

    public function assertIsNotRedirect(): void
    {
        $isRedirect = $this
            ->getClient()
            ->getResponse()
            ->isRedirect()
        ;
        $this->assertFalse($isRedirect);
    }

    public function doGet(string $url): void
    {
        $this
            ->getClient()
            ->request('GET', $url)
        ;
    }

    public function followRedirect(): void
    {
        $this
            ->getClient()
            ->followRedirect()
        ;
    }

    public function get(string $service)
    {
        return $this
            ->getContainer()
            ->get($service)
        ;
    }

    public function getCrawler(): Crawler
    {
        return $this
            ->getClient()
            ->getCrawler()
        ;
    }

    public function getHttpStatusCode(): int
    {
        return $this
            ->getClient()
            ->getResponse()
            ->getStatusCode()
        ;
    }

    public function getObjectManager(): ObjectManager
    {
        return $this
            ->getContainer()
            ->get('doctrine')
            ->getManager()
        ;
    }

    public function getSecureSession(): SecureSession
    {
        return $this
            ->getContainer()
            ->get('App\Service\SecureSession')
        ;
    }

    public function getU2fAuthenticationMocker(): U2fAuthenticationMocker
    {
        return $this
            ->getContainer()
            ->get('App\Service\Mocker\U2fAuthenticationMocker')
        ;
    }

    public function getUri(): string
    {
        return $this
            ->getClient()
            ->getRequest()
            ->getUri()
        ;
    }

    public function getUriLastPart(): string
    {
        $uri = $this
            ->getClient()
            ->getRequest()
            ->getUri()
        ;

        $pos = strrpos($uri, '/');
        $lastPart = substr($uri, $pos + 1);

        return $lastPart;
    }

    public function isRedirect(): bool
    {
        return $this
            ->getClient()
            ->getResponse()
            ->isRedirect()
        ;
    }

    public function submit(Form $form): void
    {
        $this
            ->getClient()
            ->submit($form)
        ;
    }
}
