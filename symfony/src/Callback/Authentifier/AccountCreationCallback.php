<?php

declare(strict_types=1);

namespace App\Callback\Authentifier;

use LM\AuthAbstractor\Model\IAuthenticationCallback;
use LM\AuthAbstractor\Model\IAuthenticationProcess;
use Symfony\Component\HttpFoundation\Response;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Twig_Environment;
use App\Factory\MemberFactory;
use Doctrine\ORM\EntityManagerInterface;
use LM\AuthAbstractor\Enum\Persistence\Operation;
use LM\AuthAbstractor\Model\IU2fRegistration;
use LM\AuthAbstractor\Model\IMember;
use App\Factory\U2fRegistrationFactory;

class AccountCreationCallback implements IAuthenticationCallback
{
    private $manager;

    private $memberFactory;

    private $psr7Factory;

    private $twig;

    private $u2fRegFactory;

    public function __construct(
        EntityManagerInterface $manager,
        MemberFactory $memberFactory,
        DiactorosFactory $psr7Factory,
        Twig_Environment $twig,
        U2fRegistrationFactory $u2fRegFactory
    ) {
        $this->manager = $manager;
        $this->memberFactory = $memberFactory;
        $this->psr7Factory = $psr7Factory;
        $this->twig = $twig;
        $this->u2fRegFactory = $u2fRegFactory;
    }

    public function handleFailedProcess(
        IAuthenticationProcess $process
    ): ResponseInterface {
    }

    public function handleSuccessfulProcess(
        IAuthenticationProcess $process
    ): ResponseInterface {
        $response = $this
            ->twig
            ->render(
                'messages/success.html.twig',
                [
                    'pageTitle' => 'Account created successfully',
                    'message' => 'Your account was successfully created.',
                ]
            )
        ;

        foreach ($process->getPersistOperations() as $operation) {
            $entity = $operation->getObject();
            $type = $operation->getType();
            if (
                $entity instanceof IMember &&
                $type->is(new Operation(Operation::CREATE))
            ) {
                $member = $this
                    ->memberFactory
                    ->createFrom($entity)
                ;
                $this
                    ->manager
                    ->persist($member)
                ;
            }
        }
        foreach ($process->getPersistOperations() as $operation) {
            $entity = $operation->getObject();
            $type = $operation->getType();
            if (
                $entity instanceof IU2fRegistration &&
                $type->is(new Operation(Operation::CREATE))
            ) {
                $u2fRegistration = $this
                    ->u2fRegFactory
                    ->toEntity($entity, $member)
                ;
                $this
                    ->manager
                    ->persist($u2fRegistration)
                ;
            }
        }
        $this->manager->flush();

        return $this->psr7Factory->createResponse(new Response($response));
    }
}
