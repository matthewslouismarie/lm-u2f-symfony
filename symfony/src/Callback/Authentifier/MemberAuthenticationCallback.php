<?php

namespace App\Callback\Authentifier;

use App\Entity\Member;
use App\Service\LoginForcer;
use LM\Authentifier\Model\AuthenticationProcess;
use LM\Authentifier\Model\AuthentifierResponse;
use LM\Authentifier\Model\IAuthenticationCallback;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @todo Make immutable?
 */
class MemberAuthenticationCallback implements IAuthenticationCallback
{
    private $container;

    public function handleSuccessfulProcess(AuthenticationProcess $authProcess): AuthentifierResponse
    {
        $this
            ->container
            ->get(LoginForcer::class)
            ->logUserIn(new Request(), $this
                ->container
                ->get('doctrine')
                ->getManager()
                ->getRepository(Member::class)
                ->findOneBy([
                    'username' => $authProcess->getUsername(),
                ]))
        ;

        $httpResponse = $this
            ->container
            ->get('twig')
            ->render('messages/success.html.twig', [
                'pageTitle' => 'Successful login',
                'message' => 'You logged in successfully.'
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
