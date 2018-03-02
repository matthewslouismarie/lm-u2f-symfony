<?php

namespace App\Tests\Controller;

class U2fCheckerTest implements TestCaseTemplate
{
    public function testU2f()
    {
        $tdm = (new TransitingDataManager())
            ->add(new TransitingData('checkers', 'initial_route', new ArrayObject(['ic_u2f'])))
            ->add(new TransitingData('success_route', 'initial_route', new StringObject('authentication_processing')))
        ;
        $sid = $this
            ->getSecureSession()
            ->storeObject($tdm, TransitingDataManager::class)
        ;
    }
}
