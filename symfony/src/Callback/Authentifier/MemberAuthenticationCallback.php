<?php

declare(strict_types=1);

namespace App\Callback\Authentifier;

use App\Entity\Member;
use App\Service\LoginForcer;
use LM\Authentifier\Model\AuthenticationProcess;
use LM\Authentifier\Model\AuthentifierResponse;
use Psr\Container\ContainerInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @todo Make immutable?
 */
class MemberAuthenticationCallback extends AbstractCallback
{
    public function handleSuccessfulProcess(AuthenticationProcess $authProcess): AuthentifierResponse
    {
        $this
            ->getContainer()
            ->get(LoginForcer::class)
            ->logUserIn(new Request(), $this
                ->getContainer()
                ->get('doctrine')
                ->getManager()
                ->getRepository(Member::class)
                ->findOneBy([
                    'username' => $authProcess->getUsername(),
                ]))
        ;

        $httpResponse = $this
            ->getContainer()
            ->get('twig')
            ->render('messages/success.html.twig', [
                'pageTitle' => 'Successful login',
                'message' => 'You logged in successfully.',
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
    }

    public function serialize()
    {
        return serialize([]);
    }

    public function unserialize($serialized)
    {
    }
}
