<?php

declare(strict_types=1);

namespace App\Callback\Authentifier;

use App\Repository\MemberRepository;
use App\Service\LoginForcer;
use LM\Authentifier\Model\AuthenticationProcess;
use LM\Authentifier\Model\AuthentifierResponse;
use LM\Authentifier\Model\IAuthenticationCallback;
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
        $this->failureClosure = $failureClosure;
        $this->loginForcer = $loginForcer;
        $this->memberRepository = $memberRepository;
        $this->psr7Factory = new DiactorosFactory();
        $this->twig = $twig;
    }

    public function handleFailedProcess(AuthenticationProcess $authProcess): AuthentifierResponse
    {
        return ($this->failureClosure)($authProcess);
    }

    public function handleSuccessfulProcess(AuthenticationProcess $authProcess): AuthentifierResponse
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

        return new AuthentifierResponse(
            $authProcess,
            $this
                ->psr7Factory
                ->createResponse(new Response($httpResponse))
        )
        ;
    }
}
