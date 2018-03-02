<?php

namespace App\Tests\Controller;

use App\DataStructure\TransitingDataManager;
use App\Model\ArrayObject;
use App\Model\TransitingData;

class AuthentifierTest extends TestCaseTemplate
{
    public function testUsernamePasswordChecker()
    {
        $tdm = (new TransitingDataManager())
            ->add(new TransitingData('checkers', 'initial_route', new ArrayObject(['ic_usernamepassword'])))
        ;
        $sid = $this
            ->getSecureSession()
            ->storeObject($tdm, TransitingDataManager::class)
        ;
        $this->doGet("/all/initiate-identity-check/{$sid}");
        $this->assertIsRedirect();
        $this->followRedirect();
        $this->assertEquals(
            "http://localhost/all/check-username-and-password/{$sid}",
            $this->getUri())
        ;
    }
}
