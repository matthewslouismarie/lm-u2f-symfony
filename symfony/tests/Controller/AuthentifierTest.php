<?php

namespace App\Tests\Controller;

use App\DataStructure\TransitingDataManager;
use App\Model\ArrayObject;
use App\Model\StringObject;
use App\Model\TransitingData;

class AuthentifierTest extends TestCaseTemplate
{
    public function testUsernamePasswordChecker()
    {
        $tdm = (new TransitingDataManager())
            ->add(new TransitingData('checkers', 'initial_route', new ArrayObject(['ic_credential'])))
            ->add(new TransitingData('success_route', 'initial_route', new StringObject('successful_authentication')))
        ;
        $sid = $this
            ->getSecureSession()
            ->storeObject($tdm, TransitingDataManager::class)
        ;
        $this->doGet("/all/initiate-identity-check/{$sid}");
        $this->assertIsRedirect();
        $this->followRedirect();
        $this->assertEquals(
            "http://localhost/all/check-credential/{$sid}",
            $this->getUri())
        ;
        $this->assertEquals(
            200,
            $this->getHttpStatusCode())
        ;
        $loginRequestFiller = $this->get('App\Service\Form\Filler\CredentialAuthenticationFiller');
        $this->submit(
            $loginRequestFiller->fillForm($this->getClient()->getCrawler(), 'hello', 'louis')
        );
        $this->assertIsRedirect();
    }
}
