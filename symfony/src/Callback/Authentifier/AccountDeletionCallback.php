<?php

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
    private $member;

    public function __construct(Member $member)
    {
        $this->member = $member;
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

    public function wakeUp(ContainerInterface $container): void
    {
        parent::wakeUp($container);
        $this->member = $container
            ->get('doctrine')
            ->getManager()
            ->merge($this->member)
        ;
    }

    public function serialize()
    {
        return serialize([
            $this->member,
        ]);
    }

    public function unserialize($serialized)
    {
        list(
            $this->member) = unserialize($serialized);
    }
}
