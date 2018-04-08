<?php

namespace App\Callback\Authentifier;

use App\Entity\Member;
use App\Service\LoginForcer;
use App\Security\Token\AuthenticationToken;
use App\Service\SecureSession;
use LM\Authentifier\Model\AuthenticationProcess;
use LM\Authentifier\Model\AuthentifierResponse;
use LM\Authentifier\Model\IAuthenticationCallback;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Router;
use Twig_Environment;
use Symfony\Bundle\TwigBundle\TwigEngine;

class MoneyTransferCallback implements IAuthenticationCallback
{
    private $container;

    public function handleSuccessfulProcess(AuthenticationProcess $authProcess): AuthentifierResponse
    {
        $httpResponse = $this
            ->container
            ->get('twig')
            ->render('messages/success.html.twig', [
                'pageTitle' => 'Successful money transfer',
                'message' => 'You successfully transferred money.'
            ])
        ;

        $psr7Factory = new DiactorosFactory();

        return new AuthentifierResponse(
            $authProcess,
            $psr7Factory->createResponse(new Response($httpResponse)))
        ;
    }

    public function handleFailedProcess(AuthenticationProcess $authProcess): AuthentifierResponse
    {
        return new AuthentifierResponse(
            $authProcess,
            $psr7Factory->createResponse(new Response('')))
        ;
    }

    public function wakeUp(PsrContainerInterface $container): void
    {
        $this->container = $container;
    }

    public function serialize()
    {
        return serialize([]);
    }

    public function unserialize($serialized)
    {
    }
}
