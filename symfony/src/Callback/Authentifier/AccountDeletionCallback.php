<?php

namespace App\Callback\Authentifier;

use App\Entity\Member;
use LM\Authentifier\Model\AuthenticationProcess;
use LM\Authentifier\Model\AuthentifierResponse;
use LM\Authentifier\Model\IAuthenticationCallback;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Component\HttpFoundation\Response;

class AccountDeletionCallback implements IAuthenticationCallback
{
    private $member;

    public function __construct(Member $member)
    {
        $this->member = $member;
    }

    /**
     * @todo @security The currently logged in user's password is changed. If
     * an attacker logs in and initiates the process, then lets the victim log
     * in, the attacker will then be able to achieve the process changing the
     * victim's password.
     */
    public function handleSuccessfulProcess(AuthenticationProcess $authProcess): AuthentifierResponse
    {
        $em = $this
            ->container
            ->get('doctrine')
            ->getManager()
        ;
        $em->remove($this->member);
        $em->flush();

        $httpResponse = $this
            ->container
            ->get('twig')
            ->render('messages/success.html.twig', [
                'pageTitle' => 'Successful account deletion',
                'message' => 'Your account was successfully deleted.'
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
        $this->member = $container
            ->get('doctrine')
            ->getManager()
            ->merge($this->member)
        ;
        $this->container = $container;
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
