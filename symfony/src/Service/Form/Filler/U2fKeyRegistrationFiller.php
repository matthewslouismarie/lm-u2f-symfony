<?php

namespace App\Service\Form\Filler;

use App\DataStructure\TransitingDataManager;
use App\Exception\NonexistentNodeException;
use App\Model\TransitingData;
use App\Service\Mocker\U2fRegistrationMocker;
use App\Service\SecureSession;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;

class U2fKeyRegistrationFiller
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

    public function fillForm(Crawler $crawler, string $sid): Form
    {
        $cycle = $this->mocker->getNewCycle();
        $tdm = $this
            ->secureSession
            ->getObject($sid, TransitingDataManager::class)
            ->filterBy('key', 'u2f_registration_request')
            ->add(new TransitingData(
                'u2f_registration_request',
                'add_u2f_key',
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
            'new_u2f_registration[u2fKeyName]' => 'a random name',
        ]);

        return $form;
    }
}
