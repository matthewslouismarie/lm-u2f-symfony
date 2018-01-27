<?php

namespace App\Service\Form\Filler;

use App\DataStructure\TransitingDataManager;
use App\Exception\NonexistentNodeException;
use App\Model\TransitingData;
use App\Service\Mocker\U2fRegistrationMocker;
use App\Service\SecureSession;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;

class U2fRegistrationFiller
{
    private $mocker;

    private $secureSession;

    public function __construct(
        U2fRegistrationMocker $mocker,
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
            ->filterBy('key', 'U2fKeyRequest'.$keyNo)
            ->add(new TransitingData(
                'U2fKeyRequest'.$keyNo,
                'registration_u2f_key',
                $cycle->getRequest()
            ))
        ;
        $this
            ->secureSession
            ->setObject(
                $sid,
                $tdm,
                TransitingDataManager::class
            )
        ;
        $button = $crawler->selectButton(
            'new_u2f_registration[submit]'
        );
        if (0 === $button->count()) {
            throw new NonexistentNodeException();
        }
        $form = $button->form([
            'new_u2f_registration[u2fTokenResponse]' => $cycle->getResponse(),
        ]);

        return $form;
    }
}
