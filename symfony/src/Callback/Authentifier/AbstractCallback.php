<?php

declare(strict_types=1);

namespace App\Callback\Authentifier;

use LM\Authentifier\Model\AuthenticationProcess;
use LM\Authentifier\Model\AuthentifierResponse;
use LM\Authentifier\Model\IAuthenticationCallback;
use Psr\Container\ContainerInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Component\HttpFoundation\Response;
use Twig_Environment;

abstract class AbstractCallback implements IAuthenticationCallback
{
    private $twig;

    public function __construct(Twig_Environment $twig)
    {
        $this->twig = $twig;
        $this->psr7Factory = new DiactorosFactory();
    }

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
            $this
                ->psr7Factory
                ->createResponse(new Response($html))
        )
        ;
    }
}
