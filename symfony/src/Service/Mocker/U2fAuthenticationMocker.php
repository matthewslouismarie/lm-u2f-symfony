<?php

namespace App\Service\Mocker;

use App\Model\U2fAuthenticationCycle;
use App\FormModel\U2fAuthenticationRequest;
use Firehed\U2F\SignRequest;

class U2fAuthenticationMocker
{
    private $cycles;

    private $currentIndex;

    public function __construct()
    {
        $this->cycles = [
            $this->getFirstU2fAuthenticationCycle(),
        ];
        $this->currentIndex = 0;
    }

    public function getNewCycle(): U2fAuthenticationCycle
    {
        return $this->cycles[$this->currentIndex++];
    }

    private function getFirstU2fAuthenticationCycle(): U2fAuthenticationCycle
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
        $request = new U2fAuthenticationRequest($signRequests);
        $response = '{"keyHandle":"v8IplXz0zSQUXVYjvSWNcP_70AamVDoaROr1UcREnWaARrRABftdhhaKTFsYTgOj5CH6BUYxztAN9qrU3WcBZg","clientData":"eyJ0eXAiOiJuYXZpZ2F0b3IuaWQuZ2V0QXNzZXJ0aW9uIiwiY2hhbGxlbmdlIjoiS3FUYjYxN3dYNldmTzNROWdjTVBqQSIsIm9yaWdpbiI6Imh0dHBzOi8vMTcyLjE2LjIzOC4xMCIsImNpZF9wdWJrZXkiOiJ1bnVzZWQifQ","signatureData":"AQAAAPcwRgIhAOB_AJDSVHd1byQ5Id1dVwh8AL_vJOCHq_gvoKkAvosgAiEA3IKZmYshCQ5HiGdAJgJ0UJMlbJmbui6RepGFt1y58aU"}';

        return new U2fAuthenticationCycle($request, $response);
    }
}
