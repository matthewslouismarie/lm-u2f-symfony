<?php

declare(strict_types=1);

namespace App\Callback\Authentifier;

use App\Entity\Member;
use App\Entity\U2fToken;
use Doctrine\ORM\EntityManagerInterface;
use LM\AuthAbstractor\Model\AuthenticationProcess;
use LM\AuthAbstractor\Model\IAuthenticationCallback;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig_Environment;

class AccountDeletionCallback implements IAuthenticationCallback
{
    private $failureClosure;

    private $manager;

    private $member;

    private $psr7Factory;

    private $session;

    private $tokenStorage;

    private $twig;

    public function __construct(
        FailureClosure $failureClosure,
        EntityManagerInterface $manager,
        SessionInterface $session,
        TokenStorageInterface $tokenStorage,
        Twig_Environment $twig
    ) {
        $this->failureClosure = $failureClosure;
        $this->manager = $manager;
        $this->psr7Factory = new DiactorosFactory();
        $this->session = $session;
        $this->tokenStorage = $tokenStorage;
        $this->twig = $twig;
    }

    public function handleFailedProcess(AuthenticationProcess $authProcess): ResponseInterface
    {
        return ($this->failureClosure)($authProcess);
    }

    public function handleSuccessfulProcess(AuthenticationProcess $authProcess): ResponseInterface
    {
        $u2fTokens = $this
            ->manager
            ->getRepository(U2fToken::class)
            ->findByUsername($this->member->getUsername())
        ;
        foreach ($u2fTokens as $u2fToken) {
            $this->manager->remove($u2fToken);
        }
        $this->manager->remove($this->member);
        $this->manager->flush();

        $this
            ->tokenStorage
            ->setToken(null)
        ;
        $this
            ->session
            ->invalidate()
        ;

        $httpResponse = $this
            ->twig
            ->render('messages/success.html.twig', [
                'pageTitle' => 'Successful account deletion',
                'message' => 'Your account was successfully deleted.',
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
