<?php

namespace App\Callback\Authentifier;

use App\Entity\Member;
use App\Entity\U2fToken;
use App\Factory\MemberFactory;
use App\Service\LoginForcer;
use DateTimeImmutable;
use LM\Authentifier\Enum\Persistence\Operation;
use LM\Authentifier\Model\AuthenticationProcess;
use LM\Authentifier\Model\AuthentifierResponse;
use LM\Authentifier\Model\IAuthenticationCallback;
use LM\Authentifier\Model\IU2fRegistration;
use Psr\Container\ContainerInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RegistrationCallback extends AbstractCallback
{
    public function handleSuccessfulProcess(AuthenticationProcess $authProcess): AuthentifierResponse
    {
        $member = $this
            ->getContainer()
            ->get(MemberFactory::class)
            ->createFrom($authProcess->getMember())
        ;
        $em = $this
            ->getContainer()
            ->get('doctrine')
            ->getManager()
        ;
        $em->persist($member);
        foreach ($authProcess->getPersistOperations() as $operation)
        {
            if ($operation->getType()->is(new Operation(Operation::CREATE))) {
                $object = $operation->getObject();
                if (is_a($object, IU2fRegistration::class)) {
                    $u2fToken = new U2fToken(
                        null,
                        base64_encode($object->getAttestationCertificateBinary()),
                        $object->getCounter(),
                        base64_encode($object->getKeyHandleBinary()),
                        $member,
                        new DateTimeImmutable(),
                        base64_encode($object->getPublicKeyBinary()),
                        'Unnamed'.microtime())
                    ;
                    $em->persist($u2fToken);
                }
            }
        }
        $em->flush();
        $psr7Factory = new DiactorosFactory();

        $httpResponse = $this
            ->getContainer()
            ->get('twig')
            ->render('messages/success.html.twig', [
                'pageTitle' => 'Successful account creation',
                'message' => 'Your account was successfully created.',
            ])
        ;

        return new AuthentifierResponse(
            $authProcess,
            $psr7Factory->createResponse(new Response($httpResponse)))
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
