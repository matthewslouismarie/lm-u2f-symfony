<?php

namespace App\Service\Form\Filler;

use App\Exception\NonexistentNodeException;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;

class ExistingUsernameFiller
{
    public function fillForm(Crawler $crawler, string $username): Form
    {
        $button = $crawler->selectButton('existing_username[submit]');
        if (0 === $button->count()) {
            throw new NonexistentNodeException();
        }
        $form = $button->form([
            'existing_username[username]' => $username,
        ]);
        return $form;
    }
}