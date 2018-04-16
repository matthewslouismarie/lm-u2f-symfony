<?php

namespace App\Service\Form\Filler;

use App\Exception\NonexistentNodeException;
use App\Service\Mocker\U2fAuthenticationMocker;
use App\Service\SecureSession;
use Firehed\U2F\SignRequest;
use LM\Authentifier\Model\AuthenticationProcess;
use LM\Common\Model\ArrayObject;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;

class U2fAuthenticationFiller
{
    private $mocker;

    private $secureSession;

    public function __construct(
        U2fAuthenticationMocker $mocker,
        SecureSession $secureSession
    ) {
        $this->mocker = $mocker;
        $this->secureSession = $secureSession;
    }

    public function fillForm(Crawler $crawler, string $sid): Form
    {
        $cycle = $this->mocker->getNewCycle();
        $process = $this
            ->secureSession
            ->getObject($sid, AuthenticationProcess::class)
        ;
        $this
            ->secureSession
            ->setObject(
                $sid,
                new AuthenticationProcess(
                    $process->getTypedMap()
                        ->set(
                            'u2f_sign_requests',
                            new ArrayObject($cycle->getRequest()->getSignRequests(), SignRequest::class),
                            ArrayObject::class
                        )
                ),
                AuthenticationProcess::class
            )
        ;
        $formNode = $crawler->filter("[name=\"form\"]");
        if (0 === $formNode->count()) {
            throw new NonexistentNodeException();
        }

        return $formNode->form([
            'form[u2fTokenResponse]' => $cycle->getResponse(),
        ]);
    }
}
