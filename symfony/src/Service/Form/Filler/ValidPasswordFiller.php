<?php

namespace App\Service\Form\Filler;

use App\Exception\NonexistentNodeException;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;

class ValidPasswordFiller
{
    public function fillForm(
        Crawler $crawler,
        string $password): Form
    {
        $button = $crawler->selectButton('valid_password[submit]');
        if (0 === $button->count()) {
            throw new NonexistentNodeException();
        }
        $form = $button->form([
            'valid_password[password]' => $password,
        ]);

        return $form;
    }
}
