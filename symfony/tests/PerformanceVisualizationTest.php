<?php

declare(strict_types=1);

namespace App\Tests;

use App\Repository\PageMetricRepository;
use App\Entity\PageMetric;
use LM\Common\Type\TypeCheckerTrait;

class PerformanceVisualizationTest extends TestCaseTemplate
{
    use LoginTrait;
    use TypeCheckerTrait;

    const SLUG_REGEX = '/^([a-z0-9]|((?!^)-(?<!$)))+$/';

    /**
     * @todo Test for response type.
     */
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
        $this->doGet('/admin/user-metrics-csv/'.$participantSlugs[0]);
        $this->assertSame(
            200,
            $this->getHttpStatusCode()
        );
        $lines = str_getcsv($this->getResponseContent(), "\n");

        $this->assertSame(
            count(
                $this
                ->getObjectManager()
                ->getRepository(PageMetric::class)
                ->getArray($participantSlugs[0])
            ),
            count($lines) - 1
        );

        foreach ($lines as $no => $line) {
            $row = str_getcsv($line, ',');
            if (0 === $no) {
                $this->assertSame(
                    [
                        'Page Title',
                        'Redirection',
                        'Time (s)',
                        'URL',
                    ],
                    $row
                );
            } else {
                $this->assertTrue('true' === $row[1] || 'false' === $row[1]);
                $this->assertTrue(is_numeric($row[2]));
            }
        }
    }

    public function testCsvExportWithoutRedirections()
    {
        $this->login();
        $participantSlugs = $this
            ->get(PageMetricRepository::class)
            ->getParticipantSlugs()
        ;
        $this->doGet('/admin/user-metrics-csv/'.$participantSlugs[0].'/no-redirects');
        $lines = str_getcsv($this->getResponseContent(), "\n");

        $this->assertSame(
            count(
                $this
                ->getObjectManager()
                ->getRepository(PageMetric::class)
                ->getArray($participantSlugs[0], false)
            ),
            count($lines) - 1
        );

        foreach ($lines as $no => $line) {
            $row = str_getcsv($line, ',');
            if (0 === $no) {
                $this->assertSame(
                    [
                        'Page Title',
                        'Time (s)',
                        'URL',
                    ],
                    $row
                );
            } else {
                $this->assertTrue(is_numeric($row[1]));
            }
        }
    }
}
