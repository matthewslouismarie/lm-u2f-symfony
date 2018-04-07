<?php

namespace App\Callback\Authentifier;

use App\Entity\Member;
use App\Service\LoginForcer;
use App\Security\Token\AuthenticationToken;
use App\Service\SecureSession;
use LM\Authentifier\Model\AuthenticationProcess;
use LM\Authentifier\Model\AuthentifierResponse;
use LM\Authentifier\Model\IAuthenticationCallback;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Router;

/**
 * @todo Make immutable?
 */
class MemberAuthenticationCallback implements IAuthenticationCallback
{
    private $container;

    /**
     * @todo Probably not a good way of implementing authentication.
     */
    public function handleSuccessfulProcess(AuthenticationProcess $authProcess): AuthentifierResponse
    {
        $sid = $this
            ->container
            ->get(SecureSession::class)
            ->storeObject(new AuthenticationToken($authProcess->getUsername()), AuthenticationToken::class)
        ;
        $redirectResponse = new RedirectResponse($this
            ->container
            ->get("router")
            ->generate("tmp_authentication_processing", [
                "sid" => $sid,
            ]))
        ;

        $user = $this
            ->container
            ->get('doctrine')
            ->getManager()
            ->getRepository(Member::class)
            ->findOneBy([
                'username' => $authProcess->getUsername(),
            ])
        ;

        $this
            ->container
            ->get(LoginForcer::class)
            ->logUserIn(new Request(), $user)
        ;

        $psr7Factory = new DiactorosFactory();

        return new AuthentifierResponse(
            $authProcess,
            $psr7Factory->createResponse(new Response('golle')))
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
