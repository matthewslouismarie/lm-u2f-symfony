<?php

namespace App\Tests\Controller;

use App\DataStructure\TransitingDataManager;
use App\Model\ArrayObject;
use App\Model\StringObject;
use App\Model\TransitingData;

class U2fCheckerTest extends TestCaseTemplate
{
    /**
     * @todo Use a more robust way to check that the user is authenticated.
     */
    public function testU2f()
    {
        $tdm = (new TransitingDataManager())
            ->add(new TransitingData('checkers', 'initial_route', new ArrayObject(['ic_username', 'ic_u2f'])))
            ->add(new TransitingData('success_route', 'initial_route', new StringObject('authentication_processing')))
        ;
        $sid = $this
            ->getSecureSession()
            ->storeObject($tdm, TransitingDataManager::class)
        ;
        $this->doGet("/all/initiate-identity-check/{$sid}");
        $this->followRedirect();


        $existingUsernameFiller = $this->get('App\Service\Form\Filler\ExistingUsernameFiller');
        $this->submit(
            $existingUsernameFiller->fillForm($this->getClient()->getCrawler(), 'louis'))
        ;
        $this->followRedirect();
        $u2fAuthenticationFiller = $this->get('App\Service\Form\Filler\U2fAuthenticationFiller1');
        $this->submit(
            $u2fAuthenticationFiller->fillForm($this->getClient()->getCrawler(), $sid))
        ;
        
        $this->followRedirect();
        $this->followRedirect();
        $this->assertEquals(
            'http://localhost/authenticated/successful-login',
            $this->getUri()
        );
    }
}
