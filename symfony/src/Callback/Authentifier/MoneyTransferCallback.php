<?php

declare(strict_types=1);

namespace App\Callback\Authentifier;

use LM\AuthAbstractor\Model\IAuthenticationCallback;
use LM\AuthAbstractor\Model\AuthenticationProcess;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Component\HttpFoundation\Response;
use Twig_Environment;

class MoneyTransferCallback implements IAuthenticationCallback
{
    private $failureClosure;

    private $psr7Factory;

    private $twig;

    public function __construct(
        FailureClosure $failureClosure,
        Twig_Environment $twig
    ) {
        $this->failureClosure = $failureClosure;
        $this->psr7Factory = new DiactorosFactory();
        $this->twig = $twig;
    }

    public function handleFailedProcess(AuthenticationProcess $authProcess): ResponseInterface
    {
        return ($this->failureClosure)($authProcess);
    }

    public function handleSuccessfulProcess(AuthenticationProcess $authProcess): ResponseInterface
    {
        $httpResponse = $this
            ->twig
            ->render('messages/success.html.twig', [
                'pageTitle' => 'Successful money transfer',
                'message' => 'You successfully transferred money.',
            ])
        ;

        return $this
            ->psr7Factory
            ->createResponse(new Response($httpResponse))
        ;
    }
}
