<?php

namespace App\Service;

use App\Exception\NonexistentNodeException;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;

class U2fRegistrationFiller
{
    private $mocker;

    public function __construct(U2fRegistrationMocker $mocker)
    {
        $this->mocker = $mocker;
    }

    public function fillForm(Crawler $crawler): Form
    {
        $cycle = $this->mocker->getNewCycle();
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
