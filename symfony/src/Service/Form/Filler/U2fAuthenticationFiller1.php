<?php

namespace App\Service\Form\Filler;

use App\DataStructure\TransitingDataManager;
use App\Exception\NonexistentNodeException;
use App\Model\TransitingData;
use App\Service\Mocker\U2fAuthenticationMocker;
use App\Service\SecureSession;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;

/**
 * @todo Rename to U2fAuthenticationFiller?
 */
class U2fAuthenticationFiller1
{
    private $mocker;

    private $secureSession;

    public function __construct(
        U2fAuthenticationMocker $mocker,
        SecureSession $secureSession)
    {
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
                        )),
                TransitingDataManager::class
            )
        ;
        $button = $crawler->selectButton('new_u2f_authentication[submit]');
        if (0 === $button->count()) {
            throw new NonexistentNodeException();
        }

        return $button->form([
            'new_u2f_authentication[u2fTokenResponse]' => $cycle->getResponse(),
        ]);
    }
}
