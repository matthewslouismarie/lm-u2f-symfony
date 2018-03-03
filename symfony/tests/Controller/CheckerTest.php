<?php

namespace App\Tests\Controller;

use App\DataStructure\TransitingDataManager;
use App\Model\ArrayObject;
use App\Model\Integer;
use App\Model\StringObject;
use App\Model\TransitingData;

class U2fCheckerTest extends TestCaseTemplate
{
    public function testU2f()
    {
        $tdm = (new TransitingDataManager())
            ->add(new TransitingData('checkers', 'initial_route', new ArrayObject(['ic_username', 'ic_u2f', 'authentication_processing'])))
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

    private function accessCredentialForm()
    {
        $tdm = (new TransitingDataManager())
            ->add(new TransitingData('checkers', 'initial_route', new ArrayObject(['ic_credential', 'authentication_processing'])))
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

    public function testDirectAccessToGuard()
    {
        $tdm = (new TransitingDataManager())
            ->add(new TransitingData('username', 'initial_route', new StringObject('louis')))
        ;
        $sid = $this
            ->getSecureSession()
            ->storeObject($tdm, TransitingDataManager::class)
        ;
        $this->doGet("/not-authenticated/process-login/{$sid}");
        $this->followRedirect();
        $this->assertEquals(
            'http://localhost/not-authenticated/start-login',
            $this->getUri()
        );
    }
}