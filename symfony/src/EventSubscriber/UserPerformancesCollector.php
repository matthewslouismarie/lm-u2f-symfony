<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\HttpKernel\Event\KernelEvent;
use App\Entity\PageMetric;
use App\Enum\Setting;
use App\Service\AppConfigManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class UserPerformancesCollector implements EventSubscriberInterface
{
    const SESSION_ID = "PAGE_MICROTIME";

    private $config;

    private $entityManager;

    private $microtimeAtStart;

    private $microtimeAtEnd;

    private $session;

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => 'onRequestStart',
            KernelEvents::TERMINATE => 'onRequestEnd',
        );
    }

    public function __construct(
        AppConfigManager $config,
        EntityManagerInterface $entityManager,
        SessionInterface $session
    ) {
        $this->config = $config;
        $this->entityManager = $entityManager;
        $this->session = $session;
    }

    public function onRequestStart(GetResponseEvent $event)
    {
        $microtimeFloat = microtime(true);
        $uri = $event->getRequest()->getPathInfo();
        if ($this->isRequestMonitored($event)) {
            $pageMetric = new PageMetric(
                null,
                null,
                $microtimeFloat,
                null,
                $this->config->getStringSetting(Setting::PARTICIPANT_ID),
                PageMetric::REQUEST,
                $uri
            )
            ;
            $this
                ->entityManager
                ->persist($pageMetric)
            ;
            $this
                ->entityManager
                ->flush()
            ;
        }
    }

    /**
     * @todo Should validate HTML document before, and check the title is the
     * good one.
     */
    public function onRequestEnd(PostResponseEvent $event)
    {
        $microtimeFloat = microtime(true);
        $uri = $event->getRequest()->getPathInfo();
        $crawler = new Crawler();
        $crawler->addHTMLContent($event->getResponse()->getContent());
        $titleNode = $crawler->filterXPath('//html/head/title');
        $pageTitle = 0 !== $titleNode->count() ? $titleNode->text() : null;
        if ($this->isRequestMonitored($event)) {
            $pageMetric = new PageMetric(
                null,
                $event->getResponse()->isRedirection(),
                $microtimeFloat,
                $pageTitle,
                $this->config->getStringSetting(Setting::PARTICIPANT_ID),
                PageMetric::RESPONSE,
                $uri
            )
            ;
            $this
                ->entityManager
                ->persist($pageMetric)
            ;
            $this
                ->entityManager
                ->flush()
            ;
        }
    }

    private function isRequestMonitored(KernelEvent $event): bool
    {
        $localPath = $event->getRequest()->getPathInfo();
        return false === strpos($localPath, '/admin') &&
               false === strpos($localPath, '/_wdt') &&
               $event->isMasterRequest() &&
               true === $this->config->getBoolSetting(Setting::USER_STUDY_MODE_ACTIVE)
        ;
    }
}
