<?php

declare(strict_types=1);

namespace App\Callback\Authentifier;

use App\Factory\MemberFactory;
use App\Factory\U2fRegistrationFactory;
use Doctrine\ORM\EntityManagerInterface;
use LM\Authentifier\Enum\Persistence\Operation;
use LM\Authentifier\Model\AuthenticationProcess;
use LM\Authentifier\Model\AuthentifierResponse;
use LM\Authentifier\Model\IU2fRegistration;
use Psr\Container\ContainerInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Component\HttpFoundation\Response;
use Twig_Environment;

class RegistrationCallback extends AbstractCallback
{
    private $failureClosure;

    private $manager;

    private $memberFactory;

    private $psr7Factory;

    private $twig;

    private $u2fRegistrationFactory;

    public function __construct(
        EntityManagerInterface $manager,
        FailureClosure $failureClosure,
        MemberFactory $memberFactory,
        Twig_Environment $twig,
        U2fRegistrationFactory $u2fRegistrationFactory
    ) {
        $this->failureClosure = $failureClosure;
        $this->manager = $manager;
        $this->memberFactory = $memberFactory;
        $this->psr7Factory = new DiactorosFactory();
        $this->twig = $twig;
        $this->u2fRegistrationFactory = $u2fRegistrationFactory;
    }

    public function handleFailedProcess(AuthenticationProcess $authProcess): AuthentifierResponse
    {
        return ($this->failureClosure)($authProcess);
    }

    public function handleSuccessfulProcess(AuthenticationProcess $authProcess): AuthentifierResponse
    {
        $member = $this
            ->memberFactory
            ->createFrom($authProcess->getMember())
        ;
        $this
            ->manager
            ->persist($member)
        ;
        foreach ($authProcess->getPersistOperations() as $operation) {
            if ($operation->getType()->is(new Operation(Operation::CREATE))) {
                $object = $operation->getObject();
                if (is_a($object, IU2fRegistration::class)) {
                    $u2fToken = $this
                        ->u2fRegistrationFactory
                        ->toEntity($object, $member)
                    ;
                    $this
                        ->manager
                        ->persist($u2fToken);
                }
            }
        }
        $this
            ->manager
            ->flush()
        ;

        $httpResponse = $this
            ->twig
            ->render('messages/success.html.twig', [
                'pageTitle' => 'Successful account creation',
                'message' => 'Your account was successfully created.',
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
}
