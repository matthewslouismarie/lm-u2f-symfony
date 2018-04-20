<?php

declare(strict_types=1);

namespace App\Tests;

use App\Entity\Member;
use App\Service\AppConfigManager;
use App\Service\Form\Filler\U2fAuthenticationFiller1;
use App\Service\Form\Filler\UserConfirmationFiller;
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

    public function debugResponse(): void
    {
        file_put_contents(__DIR__.'/response.html', $this->getClient()->getResponse()->getContent());
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

    public function getAppConfigManager(): AppConfigManager
    {
        return $this
            ->getContainer()
            ->get('App\Service\AppConfigManager')
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

    public function getLoggedInMember(): ?Member
    {
        $token = $this
            ->get('security.token_storage')
            ->getToken()
        ;
        if (null === $token) {
            return null;
        } else {
            return $token->getUser();
        }
    }

    /**
     * @todo Rename to getManager()
     */
    public function getObjectManager(): ObjectManager
    {
        return $this
            ->getContainer()
            ->get('doctrine')
            ->getManager()
        ;
    }

    public function getResponseContent(): string
    {
        return $this
            ->getClient()
            ->getResponse()
            ->getContent()
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

    public function getU2fAuthenticationFiller(): U2fAuthenticationFiller1
    {
        return $this
            ->getContainer()
            ->get('App\Service\Form\Filler\U2fAuthenticationFiller1')
        ;
    }

    public function getUserConfirmationFiller(): UserConfirmationFiller
    {
        return $this
            ->getContainer()
            ->get('App\Service\Form\Filler\UserConfirmationFiller')
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

    public function isAdmin(): bool
    {
        return $this
            ->get('security.authorization_checker')
            ->isGranted('ROLE_ADMIN')
        ;
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
