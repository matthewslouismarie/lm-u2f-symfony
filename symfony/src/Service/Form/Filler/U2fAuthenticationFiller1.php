<?php

declare(strict_types=1);

namespace App\Service\Form\Filler;

use App\DataStructure\TransitingDataManager;
use App\Exception\NonexistentNodeException;
use App\Model\TransitingData;
use App\Service\Mocker\U2fAuthenticationMocker;
use App\Service\SecureSession;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;

/**
 * @todo Delete.
 */
class U2fAuthenticationFiller1
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
        $tdm = $this
            ->secureSession
            ->getObject($sid, TransitingDataManager::class)
        ;
        $this
            ->secureSession
            ->setObject(
                $sid,
                $tdm
                    ->filterBy('key', 'u2f_authentication_request')
                    ->add(
                        new TransitingData(
                            'u2f_authentication_request',
                            'ic_u2f',
                            $cycle->getRequest()
                        )
                    ),
                TransitingDataManager::class
            )
        ;
        $formNode = $crawler->filter("[name=\"new_u2f_authentication\"]");
        if (0 === $formNode->count()) {
            throw new NonexistentNodeException();
        }

        return $formNode->form([
            'new_u2f_authentication[u2fTokenResponse]' => $cycle->getResponse(),
        ]);
    }
}
