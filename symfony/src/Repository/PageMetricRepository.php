<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\PageMetric;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class PageMetricRepository extends ServiceEntityRepository
{
    private $crawler;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PageMetric::class);
    }

    /**
     * @todo Refactor
     */
    public function getArray(string $participantId, bool $includeRedirections = true): array
    {
        $timeSpentArray = [];
        $pageMetrics = $this->findBy([
            "participantId" => $participantId,
        ]);
        $nResponseMetrics = count($pageMetrics) - 1;
        for ($i = 0; $i < $nResponseMetrics; ++$i) {
            if (PageMetric::RESPONSE === $pageMetrics[$i]->getType() &&
            PageMetric::REQUEST === $pageMetrics[$i + 1]->getType()) {
                if ($includeRedirections) {
                    $timeSpentArray[] = [
                        'isRedirection' => $pageMetrics[$i]->isRedirection(),
                        'localPath' => $pageMetrics[$i]->getLocalPath(),
                        'pageTitle' => $pageMetrics[$i]->getPageTitle(),
                        'timeSpent' => $pageMetrics[$i + 1]->getMicrotime() - $pageMetrics[$i]->getMicrotime(),
                    ];
                } elseif (!$pageMetrics[$i]->isRedirection()) {
                     $timeSpentArray[] = [
                        'localPath' => $pageMetrics[$i]->getLocalPath(),
                        'pageTitle' => $pageMetrics[$i]->getPageTitle(),
                        'timeSpent' => $pageMetrics[$i + 1]->getMicrotime() - $pageMetrics[$i]->getMicrotime(),
                    ];
                }
            }
        }

        return $timeSpentArray;
    }

    public function getParticipantSlugs(): array
    {
        return $this->getParticipantIdsExcept();
    }

    public function getParticipantIdsExcept(?string $participantIdToExclude = null): array
    {
        $pageMetrics = $this->findAll();
        $participantIds = [];

        foreach ($pageMetrics as $currentPageMetric) {
            if ($participantIdToExclude !== $currentPageMetric->getParticipantId()
            && false === in_array($currentPageMetric->getParticipantId(), $participantIds, true)) {
                $participantIds[] = $currentPageMetric->getParticipantId();
            }
        }

        return $participantIds;
    }
}
