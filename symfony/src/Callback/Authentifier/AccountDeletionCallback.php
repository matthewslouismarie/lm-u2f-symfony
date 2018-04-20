<?php

declare(strict_types=1);

namespace App\Callback\Authentifier;

use App\Entity\Member;
use App\Entity\U2fToken;
use LM\Authentifier\Model\AuthenticationProcess;
use LM\Authentifier\Model\AuthentifierResponse;
use Psr\Container\ContainerInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Component\HttpFoundation\Response;

class AccountDeletionCallback extends AbstractCallback
{
    private $failureClosure;

    private $member;

    public function __construct(FailureClosure $failureClosure, Member $member)
    {
        $this->failureClosure = $failureClosure;
        $this->member = $member;
    }

    public function handleFailedProcess(AuthenticationProcess $authProcess): AuthentifierResponse
    {
        return ($this->failureClosure)($authProcess);
    }

    public function handleSuccessfulProcess(AuthenticationProcess $authProcess): AuthentifierResponse
    {
        $em = $this
            ->getContainer()
            ->get('doctrine')
            ->getManager()
        ;
        $u2fTokens = $em
            ->getRepository(U2fToken::class)
            ->findByUsername($this->member->getUsername())
        ;
        foreach ($u2fTokens as $u2fToken) {
            $em->remove($u2fToken);
        }
        $em->remove($this->member);
        $em->flush();

        $this
            ->getContainer()
            ->get('security.token_storage')
            ->setToken(null)
        ;
        $this
            ->getContainer()
            ->get('session')
            ->invalidate()
        ;

        $httpResponse = $this
            ->getContainer()
            ->get('twig')
            ->render('messages/success.html.twig', [
                'pageTitle' => 'Successful account deletion',
                'message' => 'Your account was successfully deleted.',
            ])
        ;

        $psr7Factory = new DiactorosFactory();

        return new AuthentifierResponse(
            $authProcess,
            $psr7Factory->createResponse(new Response($httpResponse))
        )
        ;
    }
}
