<?php

namespace App\Callback\Authentifier;

use LM\Authentifier\Model\AuthenticationProcess;
use LM\Authentifier\Model\AuthentifierResponse;
use LM\Authentifier\Model\IAuthenticationCallback;
use Psr\Container\ContainerInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractCallback implements IAuthenticationCallback
{
    private $container;

    public function handleFailedProcess(AuthenticationProcess $authProcess): AuthentifierResponse
    {
        $html = $this
            ->getContainer()
            ->get('twig')
            ->render('messages/error.html.twig', [
                'pageTitle' => 'Unsuccessful verification',
                'message' => 'Sorry, you tried too many wrong attempts',
            ])
        ;

        return new AuthentifierResponse(
            $authProcess,
            (new DiactorosFactory())->createResponse(new Response($html))
        )
        ;
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    public function wakeUp(ContainerInterface $container): void
    {
        $this->container = $container;
    }
}
