<?php

namespace App\Callback\Authentifier;

use App\Factory\U2fRegistrationFactory;
use LM\Authentifier\Enum\Persistence\Operation;
use LM\Authentifier\Model\AuthenticationProcess;
use LM\Authentifier\Model\AuthentifierResponse;
use LM\Authentifier\Model\IU2fRegistration;
use LM\Common\Model\ArrayObject;
use Psr\Container\ContainerInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Component\HttpFoundation\Response;

class U2fDeviceRegistrationCallback extends AbstractCallback
{
    public function handleSuccessfulProcess(AuthenticationProcess $authProcess): AuthentifierResponse
    {
        $em = $this
            ->getContainer()
            ->get('doctrine')
            ->getManager()
        ;
        $member = $this
            ->getContainer()
            ->get('security.token_storage')
            ->getToken()
            ->getUser()
        ;
        $em->persist($member);
        $u2fRegistrationFactory = $this
            ->getContainer()
            ->get(U2fRegistrationFactory::class)
        ;
        foreach ($authProcess->getPersistOperations() as $operation) {
            $entity = $operation->getObject();
            if ($operation->getType()->is(new Operation(Operation::CREATE)) && is_a($entity, IU2fRegistration::class)) {
                $em->persist($u2fRegistrationFactory->toEntity($operation->getObject(), $member));
            }
        }
        $em->flush();

        $httpResponse = $this
            ->getContainer()
            ->get('twig')
            ->render('messages/success.html.twig', [
                'pageTitle' => 'U2F device added successfully',
                'message' => 'The U2F was successfully registered to your account.',
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
        return '';
    }

    public function unserialize($serialized)
    {
    }
}
