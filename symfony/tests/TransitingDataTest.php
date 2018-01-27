<?php

namespace App\Tests;

use App\DataStructure\TransitingDataManager;
use App\FormModel\CredentialAuthenticationSubmission;
use App\Model\TransitingData;
use PHPUnit\Framework\TestCase;

class TransitingDataTest extends TestCase
{
    public function test()
    {
        $tdm = new TransitingDataManager();
        $this->assertTrue(method_exists($tdm, 'add'));

        $td1 = new TransitingData(
            'td1',
            'my_first_route',
            new CredentialAuthenticationSubmission()
        );

        $newTdm = $tdm->add($td1);

        $this->assertEquals(
            $td1,
            $newTdm
                ->getBy('class', CredentialAuthenticationSubmission::class)
                ->getOnlyValue()
        );

        $this->assertEquals(
            $td1,
            $newTdm
                ->getBy('route', 'my_first_route')
                ->getOnlyValue()
        );

        $this->assertEquals(
            $td1,
            $newTdm
                ->getBy('key', 'td1')
                ->getOnlyValue()
        );

        $this->assertTrue($newTdm->getBy('route', 'blablabla')->isEmpty());
        $this->assertTrue(
            $newTdm->filterBy('route', 'my_first_route')->isEmpty()
        );
        $this->assertFalse(
            $newTdm->filterBy('route', 'blablabla')->isEmpty()
        );

        $this->assertEquals([$td1->getValue()], $newTdm->toArray());
        unset($newTdm->toArray()[0]);
        $this->assertEquals(1, $newTdm->getSize());
    }
}
