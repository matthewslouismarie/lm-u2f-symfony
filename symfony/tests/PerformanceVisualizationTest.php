<?php

namespace App\Tests;

use App\Repository\PageMetricRepository;

class PerformanceVisualizationTest extends TestCaseTemplate
{
    use LoginTrait;

    const SLUG_REGEX = '/^([a-z0-9]|((?!^)-(?<!$)))+$/';

    public function testCsvExport()
    {
        $this->login();
        $participantSlugs = $this
            ->get(PageMetricRepository::class)
            ->getParticipantSlugs()
        ;
        $this->assertGreaterThan(0, count($participantSlugs));
        foreach ($participantSlugs as $slug) {
            $this->assertRegExp(self::SLUG_REGEX, $slug);
        }
    }
}
