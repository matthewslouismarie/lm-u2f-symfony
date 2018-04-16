<?php

namespace App\Controller;

use App\Callback\Authentifier\RegistrationCallback;
use App\Service\Authentifier\MiddlewareDecorator;
use LM\Common\Enum\Scalar;
use LM\Common\Model\ArrayObject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use LM\Authentifier\Challenge\CredentialRegistrationChallenge;
use LM\Authentifier\Challenge\U2fRegistrationChallenge;

class RegistrationController extends AbstractController
{
    /**
     * @Route(
     *  "/not-authenticated/registration/{sid}",
     *  name="registration")
     */
    public function register(
        string $sid = null,
        RegistrationCallback $callback,
        MiddlewareDecorator $decorator,
        Request $httpRequest)
    {
        if (null === $sid) {
            return $decorator->createProcess(
                $callback,
                $httpRequest->get('_route'),
                new ArrayObject([
                    CredentialRegistrationChallenge::class,
                    U2fRegistrationChallenge::class
                ], Scalar::_STR))
            ;
        } else {
            return $decorator->updateProcess($httpRequest, $sid);
        }
    }
}
