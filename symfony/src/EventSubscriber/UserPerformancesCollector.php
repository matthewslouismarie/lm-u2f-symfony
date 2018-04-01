<?php

namespace App\EventSubscriber;

use Symfony\Component\HttpKernel\Event\KernelEvent;
use App\Entity\PageMetric;
use App\Enum\Setting;
use App\Service\AppConfigManager;
use Doctrine\ORM\EntityManagerInterface;
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
        SessionInterface $session)
    {
        $this->config = $config;
        $this->entityManager = $entityManager;
        $this->session = $session;
    }

    public function onRequestStart(GetResponseEvent $event)
    {
        $microtimeFloat = microtime(true);
        $uri = $event->getRequest()->getUri();
        if ($this->isRequestMonitored($event))
        {
            $pageMetric = new PageMetric(
                $microtimeFloat,
                PageMetric::REQUEST,
                $uri)
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

    public function onRequestEnd(PostResponseEvent $event)
    {
        $microtimeFloat = microtime(true);
        $uri = $event->getRequest()->getUri();
        if ($this->isRequestMonitored($event))
        {
            $pageMetric = new PageMetric(
                $microtimeFloat,
                PageMetric::RESPONSE,
                $uri)
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
        $uri = $event->getRequest()->getUri();
        return false === strpos($uri, '/admin/') &&
               false === strpos($uri, '/_wdt/') &&
               $event->isMasterRequest() &&
               true === $this->config->getBoolSetting(Setting::USER_STUDY_MODE_ACTIVE)
        ;
    }
}
