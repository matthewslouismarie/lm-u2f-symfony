<?php

namespace App\Service\Form\Filler;

use App\Exception\NonexistentNodeException;
use App\Service\U2fRegistrationMocker;
use App\Service\SubmissionStack;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;

class U2fRegistrationFiller
{
    private $mocker;

    private $stack;

    public function __construct(
        U2fRegistrationMocker $mocker,
        SubmissionStack $stack)
    {
        $this->mocker = $mocker;
        $this->stack = $stack;
    }

    public function fillForm(Crawler $crawler, string $sid, int $keyNo): Form
    {
        $cycle = $this->mocker->getNewCycle();
        $this->stack->set($sid, ($keyNo * 2) + 1, $cycle->getRequest());
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
