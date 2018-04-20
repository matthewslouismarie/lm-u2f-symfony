<?php

namespace App\Tests;

use App\Entity\PageMetric;
use App\Enum\Setting;
use App\Service\AppConfigManager;
use InvalidArgumentException;
use LM\Common\Enum\Scalar;

class PageMetricTest extends TestCaseTemplate
{
    use LoginTrait;

    private $manager;

    public function setUp()
    {
        parent::setUp();
        $this->manager = $this->getObjectManager();
        $pageMetrics = $this
            ->manager
            ->getRepository(PageMetric::class)
            ->findAll()
        ;
        foreach ($pageMetrics as $pageMetric) {
            $this
                ->manager
                ->remove($pageMetric)
            ;
        }
        $this
            ->manager
            ->flush()
        ;
    }

    public function testPageMetric()
    {
        $this
            ->get(AppConfigManager::class)
            ->set(Setting::USER_STUDY_MODE_ACTIVE, true, Scalar::_BOOL)
            ->set(Setting::PARTICIPANT_ID, 'P55', Scalar::_STR)
        ;
        $this->doGet('/');
        $this->doGet('/not-authenticated/registration');
        $this->followRedirect();
        $pageMetrics = $this
            ->manager
            ->getRepository(PageMetric::class)
            ->getArray('P55')
        ;
        $this->assertSame(
            2,
            count($pageMetrics)
        );
        $pageMetric = $pageMetrics[0];
        $this->assertSame(
            'Bank Ltd.',
            $pageMetric['pageTitle']
        );
        $this->assertSame(
            false,
            $pageMetric['isRedirection']
        );
        $pageMetric = $pageMetrics[1];
        $this->assertSame(
            true,
            $pageMetric['isRedirection']
        );
    }

    public function testNonHtmlPage()
    {
        $this->login();
        $this->doGet('/admin/export');
        $this->assertSame(200, $this->getHttpStatusCode());
    }
}
