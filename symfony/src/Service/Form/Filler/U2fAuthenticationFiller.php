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
 * @todo Delete?
 */
class U2fAuthenticationFiller
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

    public function fillForm(Crawler $crawler, string $sid, int $keyNo): Form
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
                    ->filterBy('key', 'U2fAuthenticationRequest'.$keyNo)
                    ->add(
                        new TransitingData(
                            'U2fAuthenticationRequest'.$keyNo,
                            'high_security_authorization_u2f_'.$keyNo,
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
