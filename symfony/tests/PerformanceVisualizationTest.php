<?php

namespace App\Tests;

use App\Repository\PageMetricRepository;

class PerformanceVisualizationTest extends TestCaseTemplate
{
    use LoginTrait;

    public function testCsvExport()
    {
        $this->login();
        $participantIds = $this
            ->get(PageMetricRepository::class)
            ->getParticipantIds()
        ;
        $this->assertGreaterThan(0, count($participantIds));
    }
}
