<?php

namespace App\Callback\Authentifier;

use App\Security\Token\AuthenticationToken;
use App\Service\SecureSession;
use LM\Authentifier\Model\IAuthenticationCallback;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Router;

class MemberAuthenticationCallback implements IAuthenticationCallback
{
    private $container;

    /**
     * @todo Probably not a good way of implementing authentication.
     */
    public function filterSuccessResponse(ResponseInterface $response): ResponseInterface
    {
        $sid = $this
            ->container
            ->get(SecureSession::class)
            ->storeObject(new AuthenticationToken("louis"), AuthenticationToken::class)
        ;
        $redirectResponse = new RedirectResponse($this
            ->container
            ->get("router")
            ->generate("tmp_authentication_processing", [
                "sid" => $sid,
            ]))
        ;

        $psr7Factory = new DiactorosFactory();
        return $psr7Factory->createResponse($redirectResponse);
    }

    public function filterFailureResponse(ResponseInterface $response): ResponseInterface
    {
        return $response;
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
