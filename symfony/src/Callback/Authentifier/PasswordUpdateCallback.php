<?php

declare(strict_types=1);

namespace App\Callback\Authentifier;

use Doctrine\ORM\EntityManagerInterface;
use LM\Authentifier\Model\AuthenticationProcess;
use LM\Authentifier\Model\AuthentifierResponse;
use LM\Authentifier\Model\IAuthenticationCallback;
use LM\Common\Enum\Scalar;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig_Environment;

class PasswordUpdateCallback implements IAuthenticationCallback
{
    private $failureClosure;

    private $manager;

    private $psr7Factory;

    private $tokenStorageInterface;

    private $twig;

    public function __construct(
        EntityManagerInterface $manager,
        FailureClosure $failureClosure,
        TokenStorageInterface $tokenStorage,
        Twig_Environment $twig
    ) {
        $this->failureClosure = $failureClosure;
        $this->manager = $manager;
        $this->psr7Factory = new DiactorosFactory();
        $this->tokenStorage = $tokenStorage;
        $this->twig = $twig;
    }

    public function handleFailedProcess(AuthenticationProcess $authProcess): AuthentifierResponse
    {
        return ($this->failureClosure)($authProcess);
    }

    /**
     * @todo (security) The currently logged in user's password is changed. If
     * an attacker logs in and initiates the process, then lets the victim log
     * in, the attacker will then be able to achieve the process changing the
     * victim's password.
     */
    public function handleSuccessfulProcess(AuthenticationProcess $authProcess): AuthentifierResponse
    {
        $hashOfNewPassword = $authProcess
            ->getTypedMap()
            ->get('new_password', Scalar::_STR)
        ;
        $member = $this
            ->tokenStorage
            ->getToken()
            ->getUser()
        ;

        $member->setPassword($hashOfNewPassword);

        $this
            ->manager
            ->persist($member);
        $this
            ->manager
            ->flush();

        $httpResponse = $this
            ->twig
            ->render('messages/success.html.twig', [
                'pageTitle' => 'Password update successful',
                'message' => 'You successfully updated your password.',
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
