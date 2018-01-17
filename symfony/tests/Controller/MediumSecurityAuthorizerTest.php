<?php

namespace App\Tests\Controller;

use Firehed\U2F\SignRequest;
use App\FormModel\U2fAuthenticationRequest;

class MediumSecurityAuthorizerTest extends DbWebTestCase
{
    private function bla()
    {
        $firstSignRequest = new SignRequest();
        $firstSignRequest->setAppId('https://172.16.238.10');
        $firstSignRequest->setChallenge('KqTb617wX6WfO3Q9gcMPjA');
        $firstSignRequest->setKeyHandle(base64_decode('v8IplXz0zSQUXVYjvSWNcP/70AamVDoaROr1UcREnWaARrRABftdhhaKTFsYTgOj5CH6BUYxztAN9qrU3WcBZg=='));
        $secondSignRequest = new SignRequest();
        $secondSignRequest->setAppId('https://172.16.238.10');
        $secondSignRequest->setChallenge('X1aKfzxWjSgevLKZt9qXqQ');
        $secondSignRequest->setKeyHandle(base64_decode('SlhahqO2AGMqu1KZwwVVFgLhkUaOwcuWRWVn1ITLmeq/V38yn1kfANGGrZCVl8qZSm8UF8qgyp8bGEWAVKWe1g=='));
        $thirdSignRequest = new SignRequest();
        $thirdSignRequest->setAppId('https://172.16.238.10');
        $thirdSignRequest->setChallenge('o3AwKL6B46r_UqeB0Yt7yQ');
        $thirdSignRequest->setKeyHandle(base64_decode('jAbhu+BM8X6tJs6w1YdTesNRq4GvgH9e+U8E/duqEELytOqk6pXC6n5HsGi/yMQTPkoMaU9WkaNVyEk00SElWA=='));
        $signRequests = [
            1 => $firstSignRequest,
            2 => $secondSignRequest,
            3 => $thirdSignRequest,
        ];
        return new U2fAuthenticationRequest($signRequests);
    }
    public function testLogin()
    {
        $this
            ->getClient()
            ->request('GET', '/not-authenticated/start-login')
        ;
        $this->assertTrue($this->getClient()->getResponse()->isRedirect());
        $this->getClient()->followRedirect();
        $this->assertRegExp('/\/all\/u2f-authorization\/medium-security\/[a-z0-9]+/', $this->getClient()->getRequest()->getUri());
        $button = $this
            ->getClient()
            ->getCrawler()
            ->selectButton('credential_authentication[submit]')
        ;
        $this->assertNotEquals(0, $button->count());
        $form = $button->form([
            'credential_authentication[username]' => 'louis',
            'credential_authentication[password]' => 'hello',
        ]);
        $this
            ->getClient()
            ->submit($form)
        ;
        $this->assertTrue($this->getClient()->getResponse()->isRedirect());
        $this->getClient()->followRedirect();
        $u2fButton = $this
            ->getClient()
            ->getCrawler()
            ->selectButton('new_u2f_authentication[submit]')
        ;
        $u2fForm = $u2fButton->form([
            'new_u2f_authentication[u2fTokenResponse]' => '{"keyHandle":"v8IplXz0zSQUXVYjvSWNcP_70AamVDoaROr1UcREnWaARrRABftdhhaKTFsYTgOj5CH6BUYxztAN9qrU3WcBZg","clientData":"eyJ0eXAiOiJuYXZpZ2F0b3IuaWQuZ2V0QXNzZXJ0aW9uIiwiY2hhbGxlbmdlIjoiS3FUYjYxN3dYNldmTzNROWdjTVBqQSIsIm9yaWdpbiI6Imh0dHBzOi8vMTcyLjE2LjIzOC4xMCIsImNpZF9wdWJrZXkiOiJ1bnVzZWQifQ","signatureData":"AQAAAPcwRgIhAOB_AJDSVHd1byQ5Id1dVwh8AL_vJOCHq_gvoKkAvosgAiEA3IKZmYshCQ5HiGdAJgJ0UJMlbJmbui6RepGFt1y58aU"}',
        ]);
        $submissionStack = $this->getContainer()->get('App\Service\SubmissionStack');
        $pos = strrpos($this->getClient()->getRequest()->getUri(), '/');
        $sid = substr($this->getClient()->getRequest()->getUri(), $pos + 1);
        $submissionStack->set($sid, 2, $this->bla(), U2fAuthenticationRequest::class);
    }
}