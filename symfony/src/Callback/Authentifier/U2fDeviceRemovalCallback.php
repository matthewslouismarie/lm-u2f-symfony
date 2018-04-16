<?php

namespace App\Callback\Authentifier;

use App\Entity\U2fToken;
use LM\Authentifier\Model\AuthenticationProcess;
use LM\Authentifier\Model\AuthentifierResponse;
use Psr\Container\ContainerInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Component\HttpFoundation\Response;

class U2fDeviceRemovalCallback extends AbstractCallback
{
    private $entityManager;

    private $psr7Factory;

    private $twig;

    private $u2fRegistration;

    public function __construct(U2fToken $u2fRegistration)
    {
        $this->u2fRegistration = $u2fRegistration;
    }

    public function handleSuccessfulProcess(AuthenticationProcess $authProcess): AuthentifierResponse
    {
        $this
            ->entityManager
            ->remove($this->u2fRegistration)
        ;
        $this
            ->entityManager
            ->flush()
        ;

        $httpResponse = $this
            ->twig
            ->render('messages/success.html.twig', [
                'pageTitle' => 'Successful Removal of U2F Device',
                'message' => 'They U2F Device was successfully removed from your account.',
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

    public function wakeUp(ContainerInterface $container): void
    {
        parent::wakeUp($container);
        $this->psr7Factory = new DiactorosFactory();
        $this->twig = $container->get('twig');
        $this->u2fRegistration = $container
            ->get('doctrine')
            ->getManager()
            ->merge($this->u2fRegistration)
        ;
        $this->entityManager = $container
            ->get('doctrine')
            ->getManager()
        ;
    }

    public function serialize()
    {
        return serialize([
            $this->u2fRegistration,
        ]);
    }

    public function unserialize($serialized)
    {
        list(
            $this->u2fRegistration) = unserialize($serialized);
    }
}
