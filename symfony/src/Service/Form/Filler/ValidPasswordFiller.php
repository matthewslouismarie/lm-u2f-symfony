<?php

namespace App\Service\Form\Filler;

use App\Exception\NonexistentNodeException;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;

class ValidPasswordFiller
{
    const FORM_NAME = 'form';

    public function fillForm(
        Crawler $crawler,
        string $password): Form
    {
        $button = $crawler->selectButton(self::FORM_NAME.'[submit]');
        if (0 === $button->count()) {
            throw new NonexistentNodeException();
        }
        $form = $button->form([
            self::FORM_NAME.'[password]' => $password,
        ]);

        return $form;
    }
}
