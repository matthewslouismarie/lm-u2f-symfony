<?php

declare(strict_types=1);

namespace App\Callback\Authentifier;

use App\Factory\MemberFactory;
use App\Factory\U2fRegistrationFactory;
use Doctrine\ORM\EntityManagerInterface;
use LM\AuthAbstractor\Enum\Persistence\Operation;
use LM\AuthAbstractor\Model\IAuthenticationProcess;
use LM\AuthAbstractor\Model\IAuthenticationCallback;
use LM\AuthAbstractor\Model\IU2fRegistration;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Component\HttpFoundation\Response;
use Twig_Environment;

class RegistrationCallback implements IAuthenticationCallback
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
        $this->failureClosure = $failureClosure->getClosure();
        $this->manager = $manager;
        $this->memberFactory = $memberFactory;
        $this->psr7Factory = new DiactorosFactory();
        $this->twig = $twig;
        $this->u2fRegistrationFactory = $u2fRegistrationFactory;
    }

    public function handleFailedProcess(IAuthenticationProcess $authProcess): ResponseInterface
    {
        return ($this->failureClosure)($authProcess);
    }

    public function handleSuccessfulProcess(IAuthenticationProcess $authProcess): ResponseInterface
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

        return $this
            ->psr7Factory
            ->createResponse(new Response($httpResponse))
        ;
    }
}
