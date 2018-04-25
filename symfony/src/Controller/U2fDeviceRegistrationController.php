<?php

declare(strict_types=1);

namespace App\Controller;

use App\Callback\Authentifier\U2fDeviceRegistrationCallback;
use App\Service\Authentifier\MiddlewareDecorator;
use App\Service\ChallengeSpecification;
use LM\AuthAbstractor\Challenge\U2fRegistrationChallenge;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class U2fDeviceRegistrationController extends AbstractController
{
    /**
     * @todo (security) Member should be defined when auth process is started.
     *
     * @Route(
     *  "/authenticated/add-u2f-device/{sid}",
     *  name="add_u2f_device")
     */
    public function addU2fDevice(
        string $sid = null,
        ChallengeSpecification $cs,
        U2fDeviceRegistrationCallback $callback,
        MiddlewareDecorator $decorator,
        Request $httpRequest
    ) {
        if (null === $sid) {
            return $decorator->createProcess(
                $httpRequest->get('_route'),
                $cs->getChallenges(
                    $this->getUser()->getUsername(),
                    [
                        U2fRegistrationChallenge::class,
                    ]
                ),
                $this->getUser()->getUsername()
            )
            ;
        } else {
            $callback->setMember($this->getUser());

            return $decorator->updateProcess($httpRequest, $sid, $callback);
        }
    }
}
