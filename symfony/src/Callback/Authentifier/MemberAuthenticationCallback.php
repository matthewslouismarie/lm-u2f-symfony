<?php

declare(strict_types=1);

namespace App\Callback\Authentifier;

use App\Repository\MemberRepository;
use App\Service\LoginForcer;
use LM\AuthAbstractor\Model\IAuthenticationProcess;
use LM\AuthAbstractor\Model\IAuthenticationCallback;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig_Environment;

class MemberAuthenticationCallback implements IAuthenticationCallback
{
    private $failureClosure;

    private $loginForcer;

    private $memberRepository;

    private $psr7Factory;

    private $twig;

    public function __construct(
        FailureClosure $failureClosure,
        LoginForcer $loginForcer,
        MemberRepository $memberRepository,
        Twig_Environment $twig
    ) {
        $this->failureClosure = $failureClosure->getClosure();
        $this->loginForcer = $loginForcer;
        $this->memberRepository = $memberRepository;
        $this->psr7Factory = new DiactorosFactory();
        $this->twig = $twig;
    }

    public function handleFailedProcess(IAuthenticationProcess $authProcess): ResponseInterface
    {
        return ($this->failureClosure)($authProcess);
    }

    public function handleSuccessfulProcess(IAuthenticationProcess $authProcess): ResponseInterface
    {
        $this
            ->loginForcer
            ->logUserIn(new Request(), $this
                ->memberRepository
                ->findOneBy([
                    'username' => $authProcess->getUsername(),
                ]))
        ;

        $httpResponse = $this
        ->twig
            ->render('messages/success.html.twig', [
                'pageTitle' => 'Successful login',
                'message' => 'You logged in successfully.',
            ])
        ;

        return $this
            ->psr7Factory
            ->createResponse(new Response($httpResponse))
        ;
    }
}
