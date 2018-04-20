<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\PageMetric;
use Cocur\Slugify\SlugifyInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class PageMetricRepository extends ServiceEntityRepository
{
    private $slugifier;

    public function __construct(
        RegistryInterface $registry,
        SlugifyInterface $slugifier
    ) {
        parent::__construct($registry, PageMetric::class);
        $this->slugifier = $slugifier;
    }

    public function getArray(string $participantId): array
    {
        $timeSpentArray = [];
        $pageMetrics = $this->findBy([
            "participantId" => $participantId,
        ]);
        $nResponseMetrics = count($pageMetrics) - 1;
        for ($i = 0; $i < $nResponseMetrics; ++$i) {
            if (PageMetric::RESPONSE === $pageMetrics[$i]->getType() &&
            PageMetric::REQUEST === $pageMetrics[$i + 1]->getType()) {
                $timeSpentArray[] = [
                    'timeSpent' => $pageMetrics[$i + 1]->getMicrotime() - $pageMetrics[$i]->getMicrotime(),
                    'localPath' => $pageMetrics[$i]->getLocalPath(),
                ];
            }
        }

        return $timeSpentArray;
    }

    public function getParticipantSlugs(): array
    {
        return array_map(
            [
                $this->slugifier,
                'slugify'
            ],
            $this->getParticipantIdsExcept()
        );
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
