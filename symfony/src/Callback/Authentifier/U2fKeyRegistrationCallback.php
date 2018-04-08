<?php

namespace App\Callback\Authentifier;

use LM\Authentifier\Model\AuthenticationProcess;
use LM\Authentifier\Model\AuthentifierResponse;
use LM\Authentifier\Model\IAuthenticationCallback;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Component\HttpFoundation\Response;

class U2fKeyRegistrationCallback implements IAuthenticationCallback
{
    private $container;

    private $newPassword;

    public function __construct(string $newPassword)
    {
        $this->newPassword = $newPassword;
    }

    /**
     * @todo @security The currently logged in user's password is changed. If
     * an attacker logs in and initiates the process, then lets the victim log
     * in, the attacker will then be able to achieve the process changing the
     * victim's password.
     */
    public function handleSuccessfulProcess(AuthenticationProcess $authProcess): AuthentifierResponse
    {
        $member = $this
            ->container
            ->get('security.token_storage')
            ->getToken()
            ->getUser()
        ;

        $hashedPassword = $this
            ->container
            ->get('security.password_encoder')
            ->encodePassword(
            $member,
            $this->newPassword
        );
        $member->setPassword($hashedPassword);
        $em = $this
            ->container
            ->get('doctrine')
            ->getManager()
        ;
        $em->persist($member);
        $em->flush();

        $httpResponse = $this
            ->container
            ->get('twig')
            ->render('messages/success.html.twig', [
                'pageTitle' => 'Password update successful',
                'message' => 'You successfully updated your password.'
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
        return serialize([
            $this->newPassword,
        ]);
    }

    public function unserialize($serialized)
    {
        list(
            $this->newPassword) = unserialize($serialized);
    }
}
