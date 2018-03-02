<?php

namespace App\Tests\Controller;

use App\DataStructure\TransitingDataManager;

class AuthentifierTest extends TestCaseTemplate
{
    public function testAuthentifierMaster()
    {
        $tdm = new TransitingDataManager();
        $sid = $this
            ->getSecureSession()
            ->storeObject($tdm, TransitingDataManager::class)
        ;
        $this->doGet("/all/initiate-identity-check/{$sid}");
        $this->assertEquals(200, $this->getHttpStatusCode());
    }
}
