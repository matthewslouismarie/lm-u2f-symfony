<?php

namespace App\Service\Form\Filler;

use App\DataStructure\TransitingDataManager;
use App\Exception\NonexistentNodeException;
use App\Model\TransitingData;
use App\Service\Mocker\U2fRegistrationMocker;
use App\Service\SecureSession;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;

class U2fDeviceRegistrationFiller
{
    private $mocker;

    private $secureSession;

    public function __construct(
        U2fRegistrationMocker $mocker,
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
            ->replaceByKey(new TransitingData(
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

        $formNode = $crawler->filter("[name=\"new_u2f_registration\"]");
        if (0 === $formNode->count()) {
            throw new NonexistentNodeException();
        }
        $form = $formNode->form([
            'new_u2f_registration[u2fTokenResponse]' => $cycle->getResponse(),
            'new_u2f_registration[u2fKeyName]' => "random-name-".microtime(),
        ]);

        return $form;
    }
}
