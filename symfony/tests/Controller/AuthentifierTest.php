<?php

namespace App\Tests\Controller;

use App\DataStructure\TransitingDataManager;
use App\Model\ArrayObject;
use App\Model\StringObject;
use App\Model\TransitingData;

class AuthentifierTest extends TestCaseTemplate
{
    private function accessCredentialForm()
    {
        $tdm = (new TransitingDataManager())
            ->add(new TransitingData('checkers', 'initial_route', new ArrayObject(['ic_credential'])))
            ->add(new TransitingData('success_route', 'initial_route', new StringObject('authentication_processing')))
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
    }

    public function testValidCredential()
    {
        $this->accessCredentialForm();
        $credentialAuthenticationFiller = $this->get('App\Service\Form\Filler\CredentialAuthenticationFiller');
        $this->submit(
            $credentialAuthenticationFiller->fillForm($this->getClient()->getCrawler(), 'hello', 'louis')
        );
        $this->followRedirect();
        $this->followRedirect();
        $this->assertEquals(
            'http://localhost/authenticated/successful-login',
            $this->getUri()
        );
        $this->assertEquals(
            200,
            $this->getHttpStatusCode()
        );
    }

    public function testInvalidCredential()
    {
        $this->accessCredentialForm();
        $credentialAuthenticationFiller = $this->get('App\Service\Form\Filler\CredentialAuthenticationFiller');
        $this->submit(
            $credentialAuthenticationFiller->fillForm($this->getCrawler(), 'hell', 'louis')
        );
        $this->assertFalse($this->isRedirect());
        $this->submit(
            $credentialAuthenticationFiller->fillForm($this->getCrawler(), 'hello', 'loui')
        );
        $this->assertFalse($this->isRedirect());
        $this->submit(
            $credentialAuthenticationFiller->fillForm($this->getCrawler(), '', '')
        );
        $this->assertFalse($this->isRedirect());
    }
}
