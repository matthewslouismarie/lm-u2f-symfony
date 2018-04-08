<?php

namespace App\Service\Form\Filler;

use App\Exception\NonexistentNodeException;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;

class ChallengeSpecificationFiller
{
    const FORM_NAME = 'challenge_specification';

    public function fillForm(Crawler $crawler, array $settings): Form
    {
        $button = $crawler->selectButton(self::FORM_NAME.'[submit]');
        if (0 === $button->count()) {
            throw new NonexistentNodeException();
        }
        $formInputs = [];
        foreach ($settings as $key => $setting) {
            $formInputs[self::FORM_NAME.'['.$key.']'] = $setting;
        }
        $form = $button->form($formInputs);

        return $form;
    }
}
