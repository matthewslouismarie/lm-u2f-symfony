<?php

declare(strict_types=1);

namespace App\Callback\Authentifier;

use App\Entity\Member;
use App\Factory\U2fRegistrationFactory;
use Doctrine\ORM\EntityManagerInterface;
use LM\AuthAbstractor\Enum\Persistence\Operation;
use LM\AuthAbstractor\Model\AuthenticationProcess;
use LM\AuthAbstractor\Model\AuthentifierResponse;
use LM\AuthAbstractor\Model\IAuthenticationCallback;
use LM\AuthAbstractor\Model\IU2fRegistration;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Component\HttpFoundation\Response;
use Twig_Environment;

class U2fDeviceRegistrationCallback implements IAuthenticationCallback
{
    private $failureClosure;

    private $manager;

    private $member;

    private $psr7Factory;

    private $twig;

    private $u2fRegistrationFactory;

    public function __construct(
        EntityManagerInterface $manager,
        FailureClosure $failureClosure,
        Twig_Environment $twig,
        U2fRegistrationFactory $u2fRegistrationFactory
    ) {
        $this->failureClosure = $failureClosure;
        $this->manager = $manager;
        $this->psr7Factory = new DiactorosFactory();
        $this->twig = $twig;
        $this->u2fRegistrationFactory = $u2fRegistrationFactory;
    }

    public function handleFailedProcess(AuthenticationProcess $authProcess): ResponseInterface
    {
        return ($this->failureClosure)($authProcess);
    }

    public function handleSuccessfulProcess(AuthenticationProcess $authProcess): ResponseInterface
    {
        $this
            ->manager
            ->persist($this->member)
        ;
        foreach ($authProcess->getPersistOperations() as $operation) {
            $entity = $operation->getObject();
            if ($operation->getType()->is(new Operation(Operation::CREATE)) && is_a($entity, IU2fRegistration::class)) {
                $this
                    ->manager
                    ->persist($this->u2fRegistrationFactory->toEntity($operation->getObject(), $this->member));
            }
        }
        $this
            ->manager
            ->flush();

        $httpResponse = $this
            ->twig
            ->render('messages/success.html.twig', [
                'pageTitle' => 'U2F device added successfully',
                'message' => 'The U2F was successfully registered to your account.',
            ])
        ;

        return $this
            ->psr7Factory
            ->createResponse(new Response($httpResponse))
        ;
    }

    /**
     * @todo Make immutable.
     */
    public function setMember(Member $member)
    {
        $this->member = $member;
    }
}
